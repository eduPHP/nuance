# WebSocket Events & Real-Time Updates

## Overview

Laravel Reverb (self-hosted) provides real-time updates for document analysis and rewriting progress. Users receive instant feedback without polling.

## Reverb Setup

### Installation

Already included in Laravel 12. Configure in `.env`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Start Reverb Server

```bash
php artisan reverb:start
```

For production with Docker:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

## Event Broadcasting Architecture

### Events to Broadcast

1. **DocumentAnalyzed** - AI detection completed
2. **RewriteQueued** - Rewrite job added to queue
3. **RewriteProcessing** - Rewrite job started
4. **RewriteProgress** - Progress updates during rewriting
5. **DocumentRewritten** - Rewrite completed
6. **RewriteFailed** - Rewrite job failed
7. **SampleValidated** - Sample AI check completed

## Event Implementations

### 1. DocumentAnalyzed

```php
namespace App\Modules\Detection\Events;

use App\Modules\Document\Models\Document;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentAnalyzed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public float $aiConfidence,
        public array $criticalSections
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('documents.' . $this->document->id);
    }

    public function broadcastAs(): string
    {
        return 'document.analyzed';
    }

    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->document->id,
            'ai_confidence' => $this->aiConfidence,
            'critical_sections' => $this->criticalSections,
            'analyzed_at' => now()->toISOString(),
        ];
    }
}
```

### 2. RewriteQueued

```php
class RewriteQueued implements ShouldBroadcast
{
    public function __construct(
        public Rewrite $rewrite
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('rewrites.' . $this->rewrite->id);
    }

    public function broadcastAs(): string
    {
        return 'rewrite.queued';
    }

    public function broadcastWith(): array
    {
        return [
            'rewrite_id' => $this->rewrite->id,
            'document_id' => $this->rewrite->document_id,
            'status' => 'queued',
            'queued_at' => $this->rewrite->queued_at->toISOString(),
            'estimated_time' => 30, // seconds
        ];
    }
}
```

### 3. RewriteProgress

```php
class RewriteProgress implements ShouldBroadcast
{
    public function __construct(
        public Rewrite $rewrite,
        public int $progress // 0-100
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('rewrites.' . $this->rewrite->id);
    }

    public function broadcastAs(): string
    {
        return 'rewrite.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'rewrite_id' => $this->rewrite->id,
            'progress' => $this->progress,
            'status' => 'processing',
        ];
    }
}
```

### 4. DocumentRewritten

```php
class DocumentRewritten implements ShouldBroadcast
{
    public function __construct(
        public Rewrite $rewrite
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('rewrites.' . $this->rewrite->id);
    }

    public function broadcastAs(): string
    {
        return 'rewrite.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'rewrite_id' => $this->rewrite->id,
            'document_id' => $this->rewrite->document_id,
            'status' => 'completed',
            'completed_at' => $this->rewrite->completed_at->toISOString(),
            'diff_summary' => [
                'additions' => $this->rewrite->diff['additions'],
                'deletions' => $this->rewrite->diff['deletions'],
                'similarity' => $this->rewrite->diff['similarity_percentage'],
            ],
        ];
    }
}
```

## Frontend Integration (Livewire)

### Listening to Events

```php
namespace App\Livewire\Documents;

use Livewire\Attributes\On;
use Livewire\Component;

class DocumentAnalysis extends Component
{
    public Document $document;
    public ?float $aiConfidence = null;
    public array $criticalSections = [];
    public bool $analyzing = true;

    public function mount(Document $document): void
    {
        $this->document = $document;
        
        // If already analyzed, load results
        if ($this->document->analyzed_at) {
            $this->loadAnalysisResults();
            $this->analyzing = false;
        }
    }

    #[On('echo:documents.{document.id},document.analyzed')]
    public function handleAnalyzed($data): void
    {
        $this->aiConfidence = $data['ai_confidence'];
        $this->criticalSections = $data['critical_sections'];
        $this->analyzing = false;
        
        // Show success notification
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Analysis complete!'
        ]);
    }

    public function render()
    {
        return view('livewire.documents.analysis');
    }
}
```

### Blade Template with Echo

```blade
<div 
    x-data="{ analyzing: @entangle('analyzing') }"
    wire:key="document-{{ $document->id }}"
>
    @if($analyzing)
        <div class="loading loading-spinner loading-lg"></div>
        <p>Analyzing document...</p>
    @else
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">AI Confidence</div>
                <div class="stat-value">{{ number_format($aiConfidence, 1) }}%</div>
            </div>
        </div>
        
        @if(count($criticalSections) > 0)
            <h3>Critical Sections</h3>
            @foreach($criticalSections as $section)
                <div class="alert alert-warning">
                    <p>{{ $section['text'] }}</p>
                    <small>Confidence: {{ $section['confidence'] }}%</small>
                </div>
            @endforeach
        @endif
    @endif
</div>

@script
<script>
    // Echo is automatically available via Livewire
    Echo.channel('documents.{{ $document->id }}')
        .listen('.document.analyzed', (e) => {
            console.log('Document analyzed:', e);
        });
</script>
@endscript
```

## Rewrite Progress Component

```php
class RewriteProgress extends Component
{
    public Rewrite $rewrite;
    public int $progress = 0;
    public string $status = 'queued';

    #[On('echo:rewrites.{rewrite.id},rewrite.queued')]
    public function handleQueued($data): void
    {
        $this->status = 'queued';
        $this->progress = 0;
    }

    #[On('echo:rewrites.{rewrite.id},rewrite.progress')]
    public function handleProgress($data): void
    {
        $this->progress = $data['progress'];
        $this->status = 'processing';
    }

    #[On('echo:rewrites.{rewrite.id},rewrite.completed')]
    public function handleCompleted($data): void
    {
        $this->status = 'completed';
        $this->progress = 100;
        
        // Refresh page to show diff
        $this->redirect(route('documents.show', $this->rewrite->document_id));
    }

    #[On('echo:rewrites.{rewrite.id},rewrite.failed')]
    public function handleFailed($data): void
    {
        $this->status = 'failed';
        
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Rewrite failed: ' . $data['error']
        ]);
    }

    public function render()
    {
        return view('livewire.rewrites.progress');
    }
}
```

## Channel Authorization

For private user-specific channels:

```php
// routes/channels.php

use App\Models\User;
use App\Modules\Document\Models\Document;
use App\Modules\Rewriting\Models\Rewrite;

Broadcast::channel('documents.{documentId}', function (User $user, int $documentId) {
    $document = Document::find($documentId);
    return $document && $user->id === $document->user_id;
});

Broadcast::channel('rewrites.{rewriteId}', function (User $user, int $rewriteId) {
    $rewrite = Rewrite::find($rewriteId);
    return $rewrite && $user->id === $rewrite->document->user_id;
});
```

## Performance Optimization

### Event Queuing

For non-critical events, queue broadcasting:

```php
class DocumentAnalyzed implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    
    public function __construct(
        public Document $document,
        public float $aiConfidence,
        public array $criticalSections
    ) {}
}
```

### Selective Broadcasting

Only broadcast what's necessary:

```php
public function broadcastWith(): array
{
    // Don't send full document content
    return [
        'document_id' => $this->document->id,
        'ai_confidence' => $this->aiConfidence,
        // Only send summary, not full diff
        'diff_summary' => [
            'additions' => $this->rewrite->diff['additions'],
            'deletions' => $this->rewrite->diff['deletions'],
        ],
    ];
}
```

## Testing WebSocket Events

```php
use Illuminate\Support\Facades\Event;

test('broadcasts document analyzed event', function () {
    Event::fake([DocumentAnalyzed::class]);
    
    $document = Document::factory()->create();
    
    $this->detectionService->analyze($document);
    
    Event::assertDispatched(DocumentAnalyzed::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->aiConfidence > 0;
    });
});

test('broadcasts rewrite progress events', function () {
    Event::fake([RewriteProgress::class]);
    
    $rewrite = Rewrite::factory()->create();
    
    RewriteDocumentJob::dispatch($rewrite);
    
    Event::assertDispatched(RewriteProgress::class);
});
```

## Monitoring & Debugging

### Reverb Logs

```bash
php artisan reverb:start --debug
```

### Laravel Pail

Monitor events in real-time:

```bash
php artisan pail
```

### Event Horizon (Optional)

For production monitoring, consider Laravel Horizon for queue visibility.

## Error Handling

### Connection Failures

```javascript
Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('WebSocket error:', err);
    // Show offline indicator
});

Echo.connector.pusher.connection.bind('disconnected', () => {
    // Show reconnecting indicator
});
```

### Fallback to Polling

If WebSocket fails, fall back to polling:

```php
class DocumentAnalysis extends Component
{
    public function pollForResults(): void
    {
        if ($this->document->fresh()->analyzed_at) {
            $this->loadAnalysisResults();
            $this->analyzing = false;
        }
    }
}
```

```blade
<div wire:poll.5s="pollForResults">
    <!-- Fallback polling every 5 seconds -->
</div>
```
