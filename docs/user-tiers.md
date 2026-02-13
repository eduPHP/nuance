# User Tiers & Feature Access

## Overview

The Humanizer app has two tiers: **Free** and **Pro**. Free tier provides AI detection only, while Pro tier unlocks text rewriting capabilities.

## Tier Comparison

| Feature | Free Tier | Pro Tier |
|---------|-----------|----------|
| **AI Detection** | ✅ Unlimited | ✅ Unlimited |
| **Analysis Word Limit** | ⚠️ 800 words | ✅ Unlimited |
| **Critical Section Highlighting** | ✅ Yes | ✅ Yes |
| **Document Storage** | ✅ 10 documents | ✅ Unlimited |
| **Writing Samples** | ✅ Up to 6 | ✅ Up to 6 |
| **Text Rewriting** | ❌ No | ✅ 50/month |
| **Rewrite History** | ❌ No | ✅ Keep all |
| **Diff View** | ❌ No | ✅ Yes |
| **Priority Queue** | ❌ No | ✅ Yes |
| **API Access** | ❌ No | ✅ Coming soon |
| **Support** | Community | Priority |

## Implementation

### User Model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Basic tier checks (simple getters)
    public function isFree(): bool
    {
        return $this->tier === 'free';
    }

    public function isPro(): bool
    {
        return $this->tier === 'pro';
    }

    // Relationships
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    public function rewrites()
    {
        return $this->hasManyThrough(Rewrite::class, Document::class);
    }
    
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
}
```

### Subscription Model

```php
namespace App\Modules\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected function casts(): array
    {
        return [
            'usage_reset_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if usage counters need to be reset (monthly)
     */
    public function resetUsageIfNeeded(): void
    {
        if ($this->usage_reset_at->addMonth()->isPast()) {
            $this->update([
                'rewrites_used_this_month' => 0,
                'usage_reset_at' => now(),
            ]);
        }
    }
    
    public function hasReachedDocumentLimit(): bool
    {
        if ($this->document_limit === null) {
            return false; // Unlimited
        }
        
        return $this->documents_used >= $this->document_limit;
    }
    
    public function hasReachedMonthlyRewriteLimit(): bool
    {
        $this->resetUsageIfNeeded();
        
        return $this->rewrites_used_this_month >= $this->rewrite_limit_monthly;
    }
    
    public function getAnalysisWordLimit(): ?int
    {
        return $this->analysis_word_limit;
    }
    
    public function hasAnalysisWordLimit(): bool
    {
        return $this->analysis_word_limit !== null;
    }
    
    public function incrementDocumentUsage(): void
    {
        $this->increment('documents_used');
    }
    
    public function incrementRewriteUsage(): void
    {
        $this->resetUsageIfNeeded();
    }}
```

### Authorization Policies

Laravel Policies provide a clean way to organize authorization logic around models.

#### Document Policy

```php
namespace App\Policies;

use App\Models\{User, Document}o]
 ø→ĸo
 øºþ    ;

class DocumentPolicy
{
    /**
     * Determine if user can create documents
     */
    public function create(User $user): bool
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return false;
        }
        
        return !$subscription->hasReachedDocumentLimit();
    }
    
    /**
     * Determine if user can view a document
     */
    public function view(User $user, Document $document): bool
    {
        return $user->is($document->user);
    }
    
    /**
     * Determine if user can update a document
     */
    public function update(User $user, Document $document): bool
    {
        return $user->is($document->user);
    }
    
    /**
     * Determine if user can delete a document
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->is($document->user);
    }
}
```

#### Rewrite Policy

```php
namespace App\Policies;

use App\Models\{User, Rewrite};

class RewritePolicy
{
    /**
     * Determine if user can request rewrites (Pro tier only)
     */
    public function create(User $user): bool
    {
        return $user->isPro();
    }
    
    /**
     * Determine if user can request more rewrites this month
     */
    public function createThisMonth(User $user): bool
    {
        if (!$user->isPro()) {
            return false;
        }
        
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return false;
        }
        
        return !$subscription->hasReachedMonthlyRewriteLimit();
    }
    
    /**
     * Determine if user can view a rewrite
     */
    public function view(User $user, Rewrite $rewrite): bool
    {
        return $user->is($rewrite->document->user);
    }
    
    /**
     * Determine if user can view diff (Pro only)
     */
    public function viewDiff(User $user): bool
    {
        return $user->isPro();
    }
    
    /**
     * Determine if user can view rewrite history (Pro only)
     */
    public function viewHistory(User $user): bool
    {
        return $user->isPro();
    }
}
```

#### Sample Policy

```php
namespace App\Policies;

use App\Models\{User, Sample};

class SamplePolicy
{
    /**
     * Determine if user can upload samples (max 6)
     */
    public function create(User $user): bool
    {
        return $user->samples()->count() < 6;
    }
    
    /**
     * Determine if user can delete a sample
     */
    public function delete(User $user, Sample $sample): bool
    {
        return $user->is($sample->user);
    }
}
```

#### Policy Registration

Policies are auto-discovered by Laravel if they follow the naming convention. Alternatively, register them in `AuthServiceProvider`:

```php
namespace App\Providers;

use App\Models\{Document, Rewrite, Sample};
use App\Policies\{DocumentPolicy, RewritePolicy, SamplePolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DocumentPolicy::class,
        Rewrite::class => RewritePolicy::class,
        Sample::class => SamplePolicy::class,
    ];

    public function boot(): void
    {
        // Policies are automatically registered
    }
}
```

### Tier Enforcement Service

```php
namespace App\Modules\Analytics\Services;

use App\Models\User;
use App\Modules\Analytics\Exceptions\TierLimitException;

class TierLimitService
{
    public function checkDocumentLimit(User $user): void
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            throw new TierLimitException('No active subscription found.');
        }
        
        if ($subscription->hasReachedDocumentLimit()) {
            throw new TierLimitException(
                'You have reached the maximum of ' . $subscription->document_limit . 
                ' documents. Upgrade to Pro for unlimited documents.'
            );
        }
    }

    public function checkAnalysisWordLimit(User $user, string $content): void
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            throw new TierLimitException('No active subscription found.');
        }
        
        // Pro tier has no word limit
        if (!$subscription->hasAnalysisWordLimit()) {
            return;
        }
        
        $wordCount = str_word_count($content);
        $limit = $subscription->getAnalysisWordLimit();
        
        if ($wordCount > $limit) {
            throw new TierLimitException(
                "Text exceeds the {$limit}-word limit for free tier analysis. " .
                "Your text contains {$wordCount} words. " .
                "Upgrade to Pro for unlimited analysis."
            );
        }
    }

    public function checkRewriteAccess(User $user): void
    {
        if (!Gate::forUser($user)->allows('create', Rewrite::class)) {
            throw new TierLimitException(
                'Text rewriting is only available on the Pro tier. ' .
                'Upgrade to unlock this feature.'
            );
        }
    }

    public function checkMonthlyRewriteLimit(User $user): void
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            throw new TierLimitException('No active subscription found.');
        }
        
        if ($subscription->hasReachedMonthlyRewriteLimit()) {
            throw new TierLimitException(
                'You have reached your monthly limit of ' . 
                $subscription->rewrite_limit_monthly . ' rewrites. ' .
                'Limit resets on ' . $subscription->usage_reset_at->addMonth()->format('M d, Y') . '.'
            );
        }
    }

    public function getRemainingRewrites(User $user): int
    {
        $subscription = $user->subscription;
        
        if (!$subscription || $user->isFree()) {
            return 0;
        }
        
        $subscription->resetUsageIfNeeded();
        
        return max(0, $subscription->rewrite_limit_monthly - $subscription->rewrites_used_this_month);
    }

    public function getRemainingDocuments(User $user): ?int
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return 0;
        }
        
        if ($subscription->document_limit === null) {
            return null; // Unlimited
        }

        return max(0, $subscription->document_limit - $subscription->documents_used);
    }
    
    public function getAnalysisWordLimit(User $user): ?int
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return null;
        }
        
        return $subscription->getAnalysisWordLimit();
    }
    
    public function trackDocumentCreation(User $user): void
    {
        $user->subscription?->incrementDocumentUsage();
    }
    
    public function trackRewriteUsage(User $user): void
    {
        $user->subscription?->incrementRewriteUsage();
    }
}
```

### Middleware for Tier Enforcement

```php
namespace App\Http\Middleware;

use App\Modules\Analytics\Services\TierLimitService;
use Closure;
use Illuminate\Http\Request;

class EnsureProTier
{
    public function __construct(
        private TierLimitService $tierLimitService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        try {
            $this->tierLimitService->checkRewriteAccess($request->user());
        } catch (TierLimitException $e) {
            return redirect()
                ->route('pricing')
                ->with('error', $e->getMessage());
        }

        return $next($request);
    }
}
```

### Usage in Routes

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    // Free tier routes
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::post('/documents/{document}/analyze', [DocumentController::class, 'analyze']);
    
    // Pro tier routes
    Route::middleware(['pro'])->group(function () {
        Route::post('/documents/{document}/rewrite', [DocumentController::class, 'rewrite']);
        Route::get('/rewrites/{rewrite}', [RewriteController::class, 'show']);
    });
});
```

## Livewire Components with Tier Checks

### Document Submission

```php
namespace App\Livewire\Documents;

use App\Models\Document;
use App\Modules\Analytics\Services\TierLimitService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CreateDocument extends Component
{
    public string $title = '';
    public string $content = '';

    public function __construct(
        private TierLimitService $tierLimitService
    ) {}

    public function submit(): void
    {
        // Authorize using policy
        Gate::authorize('create', Document::class);
        
        try {
            // Check tier limits
            $this->tierLimitService->checkDocumentLimit(auth()->user());
            
            // Check word count limit for analysis
            $this->tierLimitService->checkAnalysisWordLimit(auth()->user(), $this->content);
            
            // Validate
            $this->validate([
                'title' => 'required|max:255',
                'content' => 'required|min:50',
            ]);
            
            // Create document
            $document = auth()->user()->documents()->create([
                'title' => $this->title,
                'content' => $this->content,
                'status' => 'pending',
            ]);
            
            // Track usage
            $this->tierLimitService->trackDocumentCreation(auth()->user());
            
            // Queue analysis
            AnalyzeDocumentJob::dispatch($document);
            
            $this->redirect(route('documents.show', $document));
            
        } catch (TierLimitException $e) {
            $this->dispatch('show-upgrade-modal', message: $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.documents.create', [
            'canCreate' => Gate::forUser($user)->allows('create', Document::class),
            'remainingDocuments' => $this->tierLimitService->getRemainingDocuments($user),
            'analysisWordLimit' => $this->tierLimitService->getAnalysisWordLimit($user),
            'currentWordCount' => str_word_count($this->content),
        ]);
    }
}
```

### Rewrite Button

```php
namespace App\Livewire\Documents;

use App\Models\{Document, Rewrite};
use App\Modules\Analytics\Services\TierLimitService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class RewriteButton extends Component
{
    public Document $document;
    public bool $canRewrite = false;
    public ?string $limitMessage = null;

    public function mount(): void
    {
        $user = auth()->user();
        
        // Check if user can rewrite at all (Pro tier)
        if (!Gate::forUser($user)->allows('create', Rewrite::class)) {
            $this->canRewrite = false;
            $this->limitMessage = 'Text rewriting is only available on the Pro tier.';
            return;
        }
        
        // Check if user can rewrite this month
        if (!Gate::forUser($user)->allows('createThisMonth', Rewrite::class)) {
            $this->canRewrite = false;
            $subscription = $user->subscription;
            $this->limitMessage = 'You have reached your monthly limit of ' . 
                $subscription->rewrite_limit_monthly . ' rewrites. ' .
                'Limit resets on ' . $subscription->usage_reset_at->addMonth()->format('M d, Y') . '.';
            return;
        }
        
        $this->canRewrite = true;
    }

    public function rewrite(): void
    {
        if (!$this->canRewrite) {
            $this->dispatch('show-upgrade-modal');
            return;
        }

        // Proceed with rewrite
        $rewrite = app(RewritingService::class)
            ->requestRewrite($this->document, auth()->user());

        $this->redirect(route('rewrites.show', $rewrite));
    }

    public function render()
    {
        return view('livewire.documents.rewrite-button', [
            'remainingRewrites' => app(TierLimitService::class)
                ->getRemainingRewrites(auth()->user()),
        ]);
    }
}
```

## UI Components

### Upgrade Modal (DaisyUI)

```blade
<!-- resources/views/components/upgrade-modal.blade.php -->
<div 
    x-data="{ open: false }"
    @show-upgrade-modal.window="open = true"
>
    <dialog class="modal" :class="{ 'modal-open': open }">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Upgrade to Pro</h3>
            <p class="py-4">{{ $message ?? 'This feature requires a Pro subscription.' }}</p>
            
            <div class="stats stats-vertical lg:stats-horizontal shadow">
                <div class="stat">
                    <div class="stat-title">Free Tier</div>
                    <div class="stat-value text-2xl">$0</div>
                    <div class="stat-desc">AI Detection Only</div>
                </div>
                
                <div class="stat">
                    <div class="stat-title">Pro Tier</div>
                    <div class="stat-value text-2xl text-primary">$19</div>
                    <div class="stat-desc">Per month</div>
                </div>
            </div>
            
            <div class="modal-action">
                <button class="btn" @click="open = false">Cancel</button>
                <a href="{{ route('pricing') }}" class="btn btn-primary">View Plans</a>
            </div>
        </div>
        <div class="modal-backdrop" @click="open = false"></div>
    </dialog>
</div>
```

### Tier Badge

```blade
<!-- resources/views/components/tier-badge.blade.php -->
@if(auth()->user()->isPro())
    <span class="badge badge-primary badge-lg">
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        Pro
    </span>
@else
    <span class="badge badge-ghost">Free</span>
@endif
```

### Usage Stats

```blade
<!-- resources/views/livewire/dashboard/usage-stats.blade.php -->
<div class="stats shadow">
    @if(auth()->user()->isFree())
        <div class="stat">
            <div class="stat-title">Documents</div>
            <div class="stat-value">{{ $documentsUsed }} / {{ $documentLimit }}</div>
            <div class="stat-desc">
                <progress 
                    class="progress progress-primary w-56" 
                    value="{{ $documentsUsed }}" 
                    max="{{ $documentLimit }}"
                ></progress>
            </div>
        </div>
    @else
        <div class="stat">
            <div class="stat-title">Rewrites This Month</div>
            <div class="stat-value">{{ $rewritesThisMonth }} / {{ $rewriteLimit }}</div>
            <div class="stat-desc">
                Resets {{ $resetDate }}
            </div>
            <div class="stat-desc mt-2">
                <progress 
                    class="progress progress-primary w-56" 
                    value="{{ $rewritesThisMonth }}" 
                    max="{{ $rewriteLimit }}"
                ></progress>
            </div>
        </div>
    @endif
</div>
```

## Testing

```php
test('free user cannot access rewrite feature', function () {
    $user = User::factory()->create(['tier' => 'free']);
    $document = Document::factory()->for($user)->create();
    
    $this->actingAs($user)
        ->post(route('documents.rewrite', $document))
        ->assertRedirect(route('pricing'));
});

test('pro user can rewrite up to daily limit', function () {
    $user = User::factory()->create(['tier' => 'pro']);
    $document = Document::factory()->for($user)->create();
    
    // Create 50 rewrites today
    Rewrite::factory()->count(50)->create([
        'document_id' => $document->id,
        'created_at' => now(),
    ]);
    
    expect($user->hasReachedDailyRewriteLimit())->toBeTrue();
});

test('free user cannot create more than 10 documents', function () {
    $user = User::factory()->create(['tier' => 'free']);
    Document::factory()->count(10)->for($user)->create();
    
    expect($user->hasReachedDocumentLimit())->toBeTrue();
});
```

## Future: Subscription Management

For payment integration (Stripe, Paddle):

```php
// Future implementation
class SubscriptionController
{
    public function subscribe(Request $request)
    {
        $user = $request->user();
        
        // Create Stripe subscription
        $user->newSubscription('default', 'price_pro_monthly')
            ->create($request->paymentMethod);
        
        // Update tier
        $user->update(['tier' => 'pro']);
        
        return redirect()->route('dashboard')
            ->with('success', 'Welcome to Pro!');
    }
}
```
