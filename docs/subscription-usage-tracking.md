# Subscription Usage Tracking

## Overview

The Humanizer app tracks usage limits and consumption at the subscription level with automatic monthly resets. This provides flexible tier management and accurate usage metering.

## How It Works

### Subscription Table Structure

Each user has a `subscription` record that contains:

```sql
-- Usage limits (set based on tier)
document_limit INT NULL           -- NULL = unlimited (Pro)
rewrite_limit_monthly INT         -- 0 for free, 50 for pro
analysis_word_limit INT NULL      -- 800 for free, NULL = unlimited (Pro)

-- Current usage (resets monthly)
documents_used INT                -- Total documents created
rewrites_used_this_month INT      -- Rewrites in current billing period

-- Reset tracking
usage_reset_at TIMESTAMP          -- When counters were last reset
```

### Monthly Reset Logic

The system automatically resets monthly usage counters:

```php
public function resetUsageIfNeeded(): void
{
    if ($this->usage_reset_at->addMonth()->isPast()) {
        $this->update([
            'rewrites_used_this_month' => 0,
            'usage_reset_at' => now(),
        ]);
    }
}
```

**When resets happen**:
- Automatically checked before each limit validation
- Automatically checked before incrementing usage
- Resets occur exactly 1 month after `usage_reset_at`

### Tier Configuration

**Free Tier**:
```php
document_limit: 10
rewrite_limit_monthly: 0
analysis_word_limit: 800
```

**Pro Tier**:
```php
document_limit: null  // Unlimited
rewrite_limit_monthly: 50
analysis_word_limit: null  // Unlimited
```

## Usage Flow

### Document Creation

```php
// 1. Check document limit
$tierLimitService->checkDocumentLimit($user);

// 2. Check word count limit (for analysis)
$tierLimitService->checkAnalysisWordLimit($user, $content);

// 3. Create document
$document = Document::create([...]);

// 4. Track usage
$tierLimitService->trackDocumentCreation($user);
// Internally: $subscription->incrementDocumentUsage()
```

### Rewrite Request

```php
// 1. Check access (Pro tier required)
$tierLimitService->checkRewriteAccess($user);

// 2. Check monthly limit
$tierLimitService->checkMonthlyRewriteLimit($user);
// Internally: resets if needed, then checks limit

// 3. Queue rewrite job
RewriteDocumentJob::dispatch($rewrite);

// 4. Track usage (in job after completion)
$tierLimitService->trackRewriteUsage($user);
// Internally: resets if needed, then increments
```

## Displaying Usage to Users

### Get Remaining Counts

```php
// Documents (for free tier)
$remaining = $tierLimitService->getRemainingDocuments($user);
// Returns: int (e.g., 7) or null (unlimited)

// Rewrites (for pro tier)
$remaining = $tierLimitService->getRemainingRewrites($user);
// Returns: int (e.g., 35)
```

### Show Reset Date

```php
$subscription = $user->subscription;
$resetDate = $subscription->usage_reset_at->addMonth()->format('M d, Y');
// Example: "Mar 13, 2026"
```

### UI Example

```blade
<div class="stat">
    <div class="stat-title">Rewrites This Month</div>
    <div class="stat-value">
        {{ $subscription->rewrites_used_this_month }} / 
        {{ $subscription->rewrite_limit_monthly }}
    </div>
    <div class="stat-desc">
        Resets {{ $subscription->usage_reset_at->addMonth()->format('M d, Y') }}
    </div>
    <progress 
        class="progress progress-primary" 
        value="{{ $subscription->rewrites_used_this_month }}" 
        max="{{ $subscription->rewrite_limit_monthly }}"
    ></progress>
</div>
```

## Subscription Creation

When a user signs up or upgrades:

```php
Subscription::create([
    'user_id' => $user->id,
    'tier' => 'free', // or 'pro'
    'document_limit' => 10, // or null for pro
    'rewrite_limit_monthly' => 0, // or 50 for pro
    'analysis_word_limit' => 800, // or null for pro
    'documents_used' => 0,
    'rewrites_used_this_month' => 0,
    'usage_reset_at' => now(), // Start of billing period
    'starts_at' => now(),
    'ends_at' => null, // Active subscription
]);
```

## Tier Upgrade/Downgrade

### Upgrade to Pro

```php
$subscription->update([
    'tier' => 'pro',
    'document_limit' => null, // Unlimited
    'rewrite_limit_monthly' => 50,
    'analysis_word_limit' => null, // Unlimited
    // Keep existing usage counts
]);
```

### Downgrade to Free

```php
$subscription->update([
    'tier' => 'free',
    'document_limit' => 10,
    'rewrite_limit_monthly' => 0,
    'analysis_word_limit' => 800,
    // Keep existing usage counts
    // User may be over limit until next reset
]);
```

## Benefits of This Approach

1. **Accurate Metering**: Usage tracked at subscription level, not calculated on-the-fly
2. **Performance**: No need to count documents/rewrites in database queries
3. **Flexibility**: Easy to change limits per subscription (custom plans)
4. **Audit Trail**: Historical usage data preserved
5. **Automatic Resets**: No cron jobs needed, resets happen on-demand
6. **Billing Integration**: Ready for Stripe/Paddle integration

## Testing

```php
test('subscription resets monthly usage automatically', function () {
    $subscription = Subscription::factory()->create([
        'rewrites_used_this_month' => 30,
        'usage_reset_at' => now()->subMonth()->subDay(),
    ]);
    
    $subscription->resetUsageIfNeeded();
    
    expect($subscription->fresh()->rewrites_used_this_month)->toBe(0);
    expect($subscription->fresh()->usage_reset_at)->toBeGreaterThan(now()->subMinute());
});

test('tracks document creation usage', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'documents_used' => 5,
    ]);
    
    $tierLimitService->trackDocumentCreation($user);
    
    expect($subscription->fresh()->documents_used)->toBe(6);
});

test('enforces monthly rewrite limit', function () {
    $user = User::factory()->create(['tier' => 'pro']);
    $subscription = Subscription::factory()->for($user)->create([
        'rewrite_limit_monthly' => 50,
        'rewrites_used_this_month' => 50,
    ]);
    
    expect(fn() => $tierLimitService->checkMonthlyRewriteLimit($user))
        ->toThrow(TierLimitException::class);
});
```

## Migration Strategy

When implementing, create subscriptions for existing users:

```php
// In migration or seeder
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        Subscription::create([
            'user_id' => $user->id,
            'tier' => $user->tier,
            'document_limit' => $user->tier === 'pro' ? null : 10,
            'rewrite_limit_monthly' => $user->tier === 'pro' ? 50 : 0,
            'documents_used' => $user->documents()->count(),
            'rewrites_used_this_month' => 0, // Start fresh
            'usage_reset_at' => now(),
            'starts_at' => $user->created_at,
            'ends_at' => null,
        ]);
    }
});
```
