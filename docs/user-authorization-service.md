# Laravel Policies for Authorization

## Overview

Laravel Policies provide an organized way to handle authorization logic around models. Instead of using a custom service, we use Laravel's built-in authorization system with Policies and the `Gate` facade.

## Why Policies Over Custom Service?

**Before** (Custom Service):
```php
class UserAuthorizationService
{
    public function canCreateDocuments(User $user): bool { ... }
    public function canRewrite(User $user): bool { ... }
}

// Usage
if ($authService->canCreateDocuments($user)) { ... }
```

**After** (Laravel Policies):
```php
class DocumentPolicy
{
    public function create(User $user): bool { ... }
}

// Usage
Gate::authorize('create', Document::class);
// or
@can('create', App\Models\Document::class)
```

**Benefits:**
1. **Framework Integration**: Native Laravel feature with built-in helpers
2. **Convention Over Configuration**: Auto-discovery, standard naming
3. **Blade Directives**: `@can`, `@cannot`, `@canany` for clean templates
4. **Middleware Support**: `can:create,App\Models\Document` in routes
5. **Automatic Injection**: Policy methods receive model instances automatically
6. **Better Testing**: Built-in testing helpers

## Policy Structure

### Document Policy

```php
namespace App\Policies;

use App\Models\{User, Document};

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

### Rewrite Policy

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
     * Custom ability name for monthly limit check
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

### Sample Policy

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

## Usage Examples

### In Controllers

```php
use App\Models\Document;
use Illuminate\Support\Facades\Gate;

class StoreDocumentController
{
    public function __invoke(StoreDocumentRequest $request): RedirectResponse
    {
        // Throws AuthorizationException if not authorized
        Gate::authorize('create', Document::class);
        
        // Create document...
    }
}
```

### In Livewire Components

```php
use Illuminate\Support\Facades\Gate;

class CreateDocument extends Component
{
    public function submit(): void
    {
        // Authorize first (throws exception if fails)
        Gate::authorize('create', Document::class);
        
        // Proceed with creation...
    }
    
    public function render()
    {
        return view('livewire.documents.create', [
            'canCreate' => Gate::forUser(auth()->user())->allows('create', Document::class),
        ]);
    }
}
```

### In Blade Templates

```blade
{{-- Check if user can create documents --}}
@can('create', App\Models\Document::class)
    <button wire:click="submit">Create Document</button>
@else
    <a href="{{ route('pricing') }}">Upgrade to create more documents</a>
@endcan

{{-- Check if user can view/edit specific document --}}
@can('update', $document)
    <button wire:click="edit">Edit</button>
@endcan

{{-- Check if user can delete specific document --}}
@can('delete', $document)
    <button wire:click="delete">Delete</button>
@endcan

{{-- Check custom ability --}}
@can('createThisMonth', App\Models\Rewrite::class)
    <button wire:click="rewrite">Rewrite Text</button>
@else
    <p>Monthly rewrite limit reached</p>
@endcan

{{-- Multiple abilities (any) --}}
@canany(['update', 'delete'], $document)
    <div class="actions">...</div>
@endcanany

{{-- Inverse check --}}
@cannot('create', App\Models\Rewrite::class)
    <div class="upgrade-banner">Upgrade to Pro for rewrites</div>
@endcannot
```

### In Routes (Middleware)

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    // Authorize document creation
    Route::post('/documents', StoreDocumentController::class)
        ->can('create', Document::class);
    
    // Authorize viewing specific document
    Route::get('/documents/{document}', ShowDocumentController::class)
        ->can('view', 'document');
    
    // Authorize rewrite creation
    Route::post('/rewrites', StoreRewriteController::class)
        ->can('create', Rewrite::class);
});
```

### Checking in Services

```php
use Illuminate\Support\Facades\Gate;

class TierLimitService
{
    public function checkRewriteAccess(User $user): void
    {
        if (!Gate::forUser($user)->allows('create', Rewrite::class)) {
            throw new TierLimitException(
                'Text rewriting is only available on the Pro tier.'
            );
        }
    }
}
```

### Conditional Logic

```php
// Check without throwing exception
if (Gate::allows('create', Document::class)) {
    // User can create
}

if (Gate::denies('create', Document::class)) {
    // User cannot create
}

// Check for specific user
if (Gate::forUser($user)->allows('create', Document::class)) {
    // Specific user can create
}

// Check custom ability
if (Gate::allows('createThisMonth', Rewrite::class)) {
    // User can create rewrite this month
}
```

## Testing Policies

```php
use App\Models\{User, Document};
use App\Policies\DocumentPolicy;

test('free user can create documents if under limit', function () {
    $user = User::factory()->create(['tier' => 'free']);
    $subscription = Subscription::factory()->for($user)->create([
        'document_limit' => 10,
        'documents_used' => 5,
    ]);
    
    $policy = new DocumentPolicy();
    
    expect($policy->create($user))->toBeTrue();
});

test('free user cannot create documents if at limit', function () {
    $user = User::factory()->create(['tier' => 'free']);
    $subscription = Subscription::factory()->for($user)->create([
        'document_limit' => 10,
        'documents_used' => 10,
    ]);
    
    $policy = new DocumentPolicy();
    
    expect($policy->create($user))->toBeFalse();
});

test('pro user can create rewrites', function () {
    $user = User::factory()->create(['tier' => 'pro']);
    
    $policy = new RewritePolicy();
    
    expect($policy->create($user))->toBeTrue();
});

test('free user cannot create rewrites', function () {
    $user = User::factory()->create(['tier' => 'free']);
    
    $policy = new RewritePolicy();
    
    expect($policy->create($user))->toBeFalse();
});

test('user can only view their own documents', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    $document = Document::factory()->for($user)->create();
    
    $policy = new DocumentPolicy();
    
    expect($policy->view($user, $document))->toBeTrue();
    expect($policy->view($otherUser, $document))->toBeFalse();
});
```

## Policy Registration

Policies are auto-discovered if they follow naming conventions:
- `App\Policies\DocumentPolicy` for `App\Models\Document`
- `App\Policies\RewritePolicy` for `App\Models\Rewrite`

Manual registration (optional):

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

## Custom Abilities

For abilities that don't map to CRUD operations, use custom method names:

```php
class RewritePolicy
{
    // Standard CRUD
    public function create(User $user): bool { ... }
    
    // Custom abilities
    public function createThisMonth(User $user): bool { ... }
    public function viewDiff(User $user): bool { ... }
    public function viewHistory(User $user): bool { ... }
}
```

Usage:
```php
Gate::authorize('createThisMonth', Rewrite::class);

@can('viewDiff', App\Models\Rewrite::class)
    <div class="diff-viewer">...</div>
@endcan
```

## Response Messages

Customize authorization failure messages:

```php
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    public function create(User $user): Response
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return Response::deny('No active subscription found.');
        }
        
        if ($subscription->hasReachedDocumentLimit()) {
            return Response::deny(
                'Document limit reached. ' .
                'You have created ' . $subscription->documents_used . ' of ' . 
                $subscription->document_limit . ' documents.'
            );
        }
        
        return Response::allow();
    }
}
```

## Best Practices

1. **Keep Policies Focused**: One policy per model
2. **Use Standard Names**: `view`, `create`, `update`, `delete` for CRUD
3. **Custom Abilities**: Use descriptive names like `createThisMonth`, `viewDiff`
4. **Return Booleans**: For simple checks, return `true`/`false`
5. **Return Responses**: For custom messages, return `Response::allow()` or `Response::deny()`
6. **Test Thoroughly**: Write tests for all policy methods
7. **Use in Blade**: Leverage `@can` directives for clean templates
8. **Middleware**: Use `can:` middleware for route protection
