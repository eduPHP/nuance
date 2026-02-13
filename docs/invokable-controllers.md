# Invokable Controller Convention

## Default Pattern

**All HTTP controllers in this application use the invokable pattern** with a single `__invoke()` method.

## Why Invokable?

- **Single Responsibility**: Each controller handles exactly one action
- **Clear Intent**: Controller name describes what it does
- **Easier Testing**: Simpler to test single-action controllers
- **Better Organization**: Forces granular thinking about actions
- **Cleaner Routes**: Route definitions are more explicit

## Naming Convention

Controllers are named after the action they perform using the pattern:
```
{Verb}{Resource}Controller
```

Examples:
- `StoreDocumentController` - Create a new document
- `ShowDocumentController` - Display a document
- `UpdateDocumentController` - Update a document
- `DeleteDocumentController` - Delete a document
- `AnalyzeDocumentController` - Trigger analysis on a document
- `RewriteDocumentController` - Request document rewrite

## Directory Structure

Organize controllers by resource in subdirectories:

```
app/Http/Controllers/
├── Documents/
│   ├── StoreDocumentController.php
│   ├── ShowDocumentController.php
│   ├── UpdateDocumentController.php
│   ├── DeleteDocumentController.php
│   ├── AnalyzeDocumentController.php
│   └── RewriteDocumentController.php
├── Samples/
│   ├── StoreSampleController.php
│   └── DeleteSampleController.php
└── Rewrites/
    └── ShowRewriteController.php
```

## Controller Template

```php
<?php

namespace App\Http\Controllers\Documents;

use App\Http\Requests\StoreDocumentRequest;
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

## Route Definition

```php
use App\Http\Controllers\Documents;

Route::middleware(['auth'])->group(function () {
    Route::post('/documents', Documents\StoreDocumentController::class)
        ->name('documents.store');
    
    Route::get('/documents/{document}', Documents\ShowDocumentController::class)
        ->name('documents.show');
    
    Route::patch('/documents/{document}', Documents\UpdateDocumentController::class)
        ->name('documents.update');
    
    Route::delete('/documents/{document}', Documents\DeleteDocumentController::class)
        ->name('documents.destroy');
    
    Route::post('/documents/{document}/analyze', Documents\AnalyzeDocumentController::class)
        ->name('documents.analyze');
    
    Route::middleware(['pro'])->group(function () {
        Route::post('/documents/{document}/rewrite', Documents\RewriteDocumentController::class)
            ->name('documents.rewrite');
    });
});
```

## Creating Controllers

Use Artisan to generate invokable controllers:

```bash
# Create invokable controller
php artisan make:controller Documents/StoreDocumentController --invokable

# Create with form request
php artisan make:controller Documents/StoreDocumentController --invokable --request=StoreDocumentRequest
```

## When NOT to Use Invokable

The only exception is for tightly-coupled resource controllers that genuinely need all CRUD operations together (rare). Even then, consider if splitting into invokable controllers would be clearer.

**Prefer invokable as the default.**

## Testing Invokable Controllers

```php
use App\Http\Controllers\Documents\StoreDocumentController;

test('stores document successfully', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->post(action(StoreDocumentController::class), [
            'title' => 'My Document',
            'content' => 'Document content here...',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
    
    expect($user->documents)->toHaveCount(1);
});
```
