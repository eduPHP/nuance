# Humanizer Application Architecture

## Overview

The Humanizer application is built as a **modular monolith** following **SOLID principles**. This architecture provides clear separation of concerns while maintaining the simplicity of a single deployable unit.

## Technology Stack

- **Backend**: Laravel 12 with PHP 8.4
- **Frontend**: Livewire 4 + DaisyUI
- **Admin Panel**: Filament 5
- **Database**: PostgreSQL
- **Queue**: Laravel Queue (database driver)
- **WebSockets**: Laravel Reverb (self-hosted)
- **AI Services**: Google Gemini (decoupled for easy switching)
- **Testing**: Pest 4

## Modular Monolith Structure

```
app/
├── Modules/
│   ├── Document/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Jobs/
│   │   ├── Events/
│   │   └── Contracts/
│   ├── Sample/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Contracts/
│   ├── Detection/
│   │   ├── Services/
│   │   ├── Analyzers/
│   │   ├── Contracts/
│   │   └── DTOs/
│   ├── Rewriting/
│   │   ├── Services/
│   │   ├── Jobs/
│   │   ├── Events/
│   │   └── Contracts/
│   └── Analytics/
│       ├── Services/
│       ├── Models/
│       └── Contracts/
```

## Module Responsibilities

### Document Module
**Purpose**: Manage user documents submitted for analysis and rewriting

**Responsibilities**:
- Document CRUD operations
- Document status management (pending, analyzed, rewriting, completed)
- Document versioning (original vs rewritten)
- Document ownership and permissions

**Key Classes**:
- `Document` (Model)
- `DocumentRepository` (Data access)
- `DocumentService` (Business logic)
- `DocumentCreated` (Event)

### Sample Module
**Purpose**: Manage user writing samples used as basis for rewriting

**Responsibilities**:
- Sample CRUD operations
- Sample validation (AI detection on upload)
- Sample-to-user relationship management
- Sample quality scoring

**Key Classes**:
- `Sample` (Model)
- `SampleRepository` (Data access)
- `SampleService` (Business logic)
- `SampleAnalyzer` (AI detection for samples)

### Detection Module
**Purpose**: Analyze text to detect AI-generated content

**Responsibilities**:
- Mathematical analysis (perplexity, burstiness)
- Text pattern recognition
- Confidence scoring
- Critical section identification
- Result storage and caching

**Key Classes**:
- `DetectionServiceInterface` (Contract)
- `MathematicalDetectionService` (Implementation)
- `PerplexityAnalyzer`
- `BurstinessAnalyzer`
- `DetectionResult` (DTO)

### Rewriting Module
**Purpose**: Rewrite AI-generated text using user samples as style guide

**Responsibilities**:
- Queue rewriting jobs
- Integrate with AI service (Gemini)
- Apply user writing style from samples
- Generate diff between original and rewritten
- Broadcast completion events via WebSocket

**Key Classes**:
- `RewritingServiceInterface` (Contract)
- `GeminiRewritingService` (Implementation)
- `RewriteDocumentJob` (Queue job)
- `DocumentRewritten` (Event)
- `RewritingResult` (DTO)

### Analytics Module
**Purpose**: Track usage, limits, and provide insights

**Responsibilities**:
- Track API usage per user
- Enforce tier limits (free vs pro)
- Generate usage reports
- Monitor system health

**Key Classes**:
- `UsageTracker`
- `TierLimitService`
- `AnalyticsRepository`

## SOLID Principles Application

### Single Responsibility Principle (SRP)
- Each module handles one domain concern
- Services have single, well-defined purposes
- Analyzers focus on specific metrics

### Open/Closed Principle (OCP)
- AI services use interfaces for easy switching
- New analyzers can be added without modifying existing code
- Detection strategies are pluggable

### Liskov Substitution Principle (LSP)
- All AI service implementations are interchangeable via interfaces
- Repository implementations can be swapped

### Interface Segregation Principle (ISP)
- Small, focused interfaces (e.g., `DetectionServiceInterface`, `RewritingServiceInterface`)
- Clients depend only on methods they use

### Dependency Inversion Principle (DIP)
- High-level modules depend on abstractions (interfaces)
- AI service implementations are injected via container
- Easy to mock for testing

## Invokable Controller Pattern

**All HTTP controllers use the invokable pattern** with a single `__invoke()` method. This enforces single responsibility and makes routing clearer.

### Why Invokable Controllers?

- **Single Responsibility**: Each controller does one thing
- **Clear Routing**: Route names match controller names
- **Easy Testing**: Simpler to test single-action controllers
- **Better Organization**: Forces you to think about action granularity

### Controller Structure

```php
namespace App\Http\Controllers\Documents;

use App\Modules\Document\Models\Document;
use App\Modules\Document\Services\DocumentService;
use Illuminate\Http\RedirectResponse;

class StoreDocumentController
{
    public function __construct(
        private DocumentService $documentService
    ) {}

    public function __invoke(StoreDocumentRequest $request): RedirectResponse
    {
        $document = $this->documentService->create(
            user: $request->user(),
            data: $request->validated()
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document created successfully');
    }
}
```

### Naming Convention

Controllers are named after the action they perform:

- `StoreDocumentController` - Create a document
- `ShowDocumentController` - Display a document
- `UpdateDocumentController` - Update a document
- `DeleteDocumentController` - Delete a document
- `AnalyzeDocumentController` - Trigger analysis
- `RewriteDocumentController` - Request rewrite

### Routing Example

```php
// routes/web.php
use App\Http\Controllers\Documents;

Route::middleware(['auth'])->group(function () {
    Route::post('/documents', Documents\StoreDocumentController::class)
        ->name('documents.store');
    
    Route::get('/documents/{document}', Documents\ShowDocumentController::class)
        ->name('documents.show');
    
    Route::post('/documents/{document}/analyze', Documents\AnalyzeDocumentController::class)
        ->name('documents.analyze');
    
    Route::middleware(['pro'])->group(function () {
        Route::post('/documents/{document}/rewrite', Documents\RewriteDocumentController::class)
            ->name('documents.rewrite');
    });
});
```

### When NOT to Use Invokable

The only exception is for resource controllers that truly need all CRUD operations and are tightly coupled (rare). In such cases, use standard resource controllers, but prefer invokable as the default.


## Service Layer Pattern

All business logic resides in service classes, keeping controllers and Livewire components thin. Controllers (invokable) and Livewire components should only:
- Validate input
- Call service methods
- Return responses

```php
// Invokable Controller (thin)
class AnalyzeDocumentController
{
    public function __construct(
        private DetectionServiceInterface $detectionService
    ) {}
    
    public function __invoke(Document $document): RedirectResponse
    {
        $result = $this->detectionService->analyze($document);
        
        return redirect()
            ->route('documents.show', $document)
            ->with('analysis', $result);
    }
}

// Service (business logic)
class MathematicalDetectionService implements DetectionServiceInterface
{
    public function analyze(Document $document): DetectionResult
    {
        // Complex analysis logic here
    }
}
```

## Repository Pattern

Data access is abstracted through repositories:

```php
interface DocumentRepositoryInterface
{
    public function findByUser(User $user): Collection;
    public function findPendingRewrites(): Collection;
    public function store(array $data): Document;
}
```

## Event-Driven Architecture

Key events for decoupling and real-time updates:

- `DocumentCreated` → Trigger analysis
- `DocumentAnalyzed` → Update UI, check if rewrite requested
- `RewriteQueued` → Notify user
- `DocumentRewritten` → Broadcast via WebSocket, update UI
- `SampleUploaded` → Validate sample quality

## Dependency Injection

All services are registered in service providers and injected via constructor:

```php
class DocumentService
{
    public function __construct(
        private DocumentRepositoryInterface $repository,
        private DetectionServiceInterface $detector,
        private EventDispatcher $events
    ) {}
}
```

## Testing Strategy

- **Unit Tests**: Test services, analyzers, and repositories in isolation
- **Feature Tests**: Test complete user flows
- **Integration Tests**: Test module interactions
- **Pest Architecture Tests**: Enforce architectural rules

## Scalability Considerations

- **Queue Workers**: Horizontal scaling for rewriting jobs
- **Database**: PostgreSQL with proper indexing
- **Caching**: Redis for detection results and sample analysis
- **WebSockets**: Reverb can scale with multiple servers
- **Module Extraction**: Modules can be extracted to microservices if needed

## Security

- **Authorization**: Policies for document and sample access
- **Rate Limiting**: Per-user API limits based on tier
- **Input Validation**: Form requests for all user input
- **Queue Security**: Encrypted job payloads for sensitive data
