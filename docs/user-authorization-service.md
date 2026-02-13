# User Authorization Service

## Overview

The `UserAuthorizationService` is a dedicated service that handles all user permission checks. This follows the Single Responsibility Principle by keeping authorization logic separate from the User model.

## Why Use a Service?

**Before** (Anti-pattern):
```php
// User model becomes bloated with authorization logic
class User extends Authenticatable
{
    public function canRewrite(): bool { ... }
    public function canCreateDocuments(): bool { ... }
    public function canUploadSamples(): bool { ... }
    public function canViewDiff(): bool { ... }
    // ... many more permission methods
}
```

**After** (Better):
```php
// User model stays clean with only relationships and basic getters
class User extends Authenticatable
{
    public function isFree(): bool { return $this->tier === 'free'; }
    public function isPro(): bool { return $this->tier === 'pro'; }
    // Relationships only
}

// Authorization logic in dedicated service
class UserAuthorizationService
{
    public function canRewrite(User $user): bool { ... }
    public function canCreateDocuments(User $user): bool { ... }
    // ... all permission checks
}
```

## Benefits

1. **Separation of Concerns**: User model handles data, service handles authorization
2. **Testability**: Easy to mock and test authorization logic independently
3. **Reusability**: Service can be injected anywhere (controllers, Livewire, jobs)
4. **Maintainability**: All permission logic in one place
5. **Flexibility**: Easy to add complex authorization rules without bloating the model

## Service Methods

### canCreateDocuments(User $user): bool

Check if user can create more documents based on their subscription limit.

```php
$authService->canCreateDocuments($user);
// Returns: true if under limit, false if at/over limit
```

**Use in**:
- Document creation forms
- Conditional UI rendering
- Middleware

### canRewrite(User $user): bool

Check if user has access to rewriting feature (Pro tier only).

```php
$authService->canRewrite($user);
// Returns: true if Pro, false if Free
```

**Use in**:
- Rewrite button visibility
- Route middleware
- Feature gates

### canRewriteThisMonth(User $user): bool

Check if user can request more rewrites this month (checks both tier and monthly limit).

```php
$authService->canRewriteThisMonth($user);
// Returns: true if Pro and under monthly limit
```

**Use in**:
- Rewrite button state
- Before queuing rewrite jobs
- Usage warnings

### canUploadSamples(User $user): bool

Check if user can upload more writing samples (max 6).

```php
$authService->canUploadSamples($user);
// Returns: true if < 6 samples
```

**Use in**:
- Sample upload forms
- Sample management UI

### canViewDiff(User $user): bool

Check if user can access diff view (Pro tier only).

```php
$authService->canViewDiff($user);
// Returns: true if Pro
```

**Use in**:
- Diff view component
- Navigation links

### canViewRewriteHistory(User $user): bool

Check if user can access rewrite history (Pro tier only).

```php
$authService->canViewRewriteHistory($user);
// Returns: true if Pro
```

**Use in**:
- History page access
- Navigation menu

## Usage Examples

### In Controllers

```php
use App\Modules\Analytics\Services\UserAuthorizationService;

class StoreDocumentController
{
    public function __construct(
        private UserAuthorizationService $authService
    ) {}
    
    public function __invoke(StoreDocumentRequest $request): RedirectResponse
    {
        if (!$this->authService->canCreateDocuments($request->user())) {
            return redirect()
                ->route('pricing')
                ->with('error', 'Document limit reached. Upgrade to Pro.');
        }
        
        // Create document...
    }
}
```

### In Livewire Components

```php
use App\Modules\Analytics\Services\UserAuthorizationService;

class CreateDocument extends Component
{
    public function __construct(
        private UserAuthorizationService $authService
    ) {}
    
    public function render()
    {
        return view('livewire.documents.create', [
            'canCreate' => $this->authService->canCreateDocuments(auth()->user()),
        ]);
    }
}
```

### In Blade Templates

```blade
@inject('authService', 'App\Modules\Analytics\Services\UserAuthorizationService')

@if($authService->canRewrite(auth()->user()))
    <button wire:click="rewrite">Rewrite Text</button>
@else
    <a href="{{ route('pricing') }}" class="btn btn-primary">
        Upgrade to Rewrite
    </a>
@endif
```

### In Middleware

```php
class EnsureCanRewrite
{
    public function __construct(
        private UserAuthorizationService $authService
    ) {}
    
    public function handle(Request $request, Closure $next)
    {
        if (!$this->authService->canRewrite($request->user())) {
            abort(403, 'This feature requires a Pro subscription.');
        }
        
        return $next($request);
    }
}
```

## Relationship with TierLimitService

**UserAuthorizationService**: Answers "Can the user do X?" (boolean checks)
**TierLimitService**: Enforces limits and throws exceptions

```php
// Authorization Service - Check permission
if ($authService->canRewrite($user)) {
    // Show rewrite button
}

// Tier Limit Service - Enforce limit (throws exception)
try {
    $tierLimitService->checkRewriteAccess($user);
    // Proceed with rewrite
} catch (TierLimitException $e) {
    // Handle error
}
```

**When to use which**:
- Use `UserAuthorizationService` for **UI conditionals** and **soft checks**
- Use `TierLimitService` for **enforcement** and **exception handling**

## Testing

```php
use App\Modules\Analytics\Services\UserAuthorizationService;

test('free user cannot rewrite', function () {
    $user = User::factory()->create(['tier' => 'free']);
    $authService = app(UserAuthorizationService::class);
    
    expect($authService->canRewrite($user))->toBeFalse();
});

test('pro user can rewrite if under monthly limit', function () {
    $user = User::factory()->create(['tier' => 'pro']);
    $subscription = Subscription::factory()->for($user)->create([
        'rewrite_limit_monthly' => 50,
        'rewrites_used_this_month' => 30,
    ]);
    
    $authService = app(UserAuthorizationService::class);
    
    expect($authService->canRewriteThisMonth($user))->toBeTrue();
});

test('pro user cannot rewrite if at monthly limit', function () {
    $user = User::factory()->create(['tier' => 'pro']);
    $subscription = Subscription::factory()->for($user)->create([
        'rewrite_limit_monthly' => 50,
        'rewrites_used_this_month' => 50,
    ]);
    
    $authService = app(UserAuthorizationService::class);
    
    expect($authService->canRewriteThisMonth($user))->toBeFalse();
});
```

## Service Registration

Register in `AppServiceProvider` or `HumanizerServiceProvider`:

```php
namespace App\Providers;

use App\Modules\Analytics\Services\UserAuthorizationService;
use Illuminate\Support\ServiceProvider;

class HumanizerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserAuthorizationService::class);
    }
}
```

## Adding New Permissions

When adding new features that require authorization:

1. Add method to `UserAuthorizationService`
2. Write tests for the new permission
3. Use in controllers/Livewire/Blade as needed

```php
// Example: Adding API access check
public function canAccessApi(User $user): bool
{
    return $user->isPro() && $user->api_enabled;
}
```
