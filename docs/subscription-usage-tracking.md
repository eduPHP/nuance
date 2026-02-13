# Subscription Usage Tracking

## Overview

The Humanizer app tracks usage limits and consumption at the subscription level with automatic monthly and daily resets. This provides flexible tier management and accurate usage metering across three tiers: Free, Pro, and Team.

## How It Works

### Subscription Table Structure

Each user has a `subscription` record that contains:

```sql
-- Usage limits (set based on tier)
document_limit INT NULL           -- NULL = unlimited (Pro/Team)
rewrite_limit_monthly INT         -- 0 for free, 50 for pro, unlimited for team
analysis_word_limit INT NULL      -- 800 for free, 10000 for pro/team
daily_analysis_limit INT NULL     -- 5 for free, NULL = unlimited (Pro/Team)

-- Current usage (resets monthly)
documents_used INT                -- Total documents created
rewrites_used_this_month INT      -- Rewrites in current billing period

-- Current usage (resets daily)
analyses_used_today INT           -- Analyses performed today

-- Reset tracking
usage_reset_at TIMESTAMP          -- When monthly counters were last reset
analysis_reset_at TIMESTAMP       -- When daily analysis counter was last reset

-- Tier features
accuracy_tier VARCHAR(50)         -- 'standard' or 'premium'
team_member_limit INT             -- 1 for free/pro, 5 for team
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

### Daily Reset Logic

The system automatically resets daily analysis counters:

```php
public function resetDailyAnalysisIfNeeded(): void
{
    if ($this->analysis_reset_at->addDay()->isPast()) {
        $this->update([
            'analyses_used_today' => 0,
            'analysis_reset_at' => now()->startOfDay(),
        ]);
    }
}
```

**When resets happen**:
- Automatically checked before each analysis
- Resets at midnight (start of day)

### Tier Configuration

**Free Tier**:
```php
[
    'tier' => 'free',
    'price' => 0,
    'document_limit' => 10,
    'analysis_word_limit' => 800,
    'daily_analysis_limit' => 5,
    'rewrite_limit_monthly' => 0,
    'accuracy_tier' => 'standard',
    'team_member_limit' => 1,
]
```

**Pro Tier**:
```php
[
    'tier' => 'pro',
    'price' => 12,
    'document_limit' => null,        // Unlimited
    'analysis_word_limit' => 10000,
    'daily_analysis_limit' => null,  // Unlimited
    'rewrite_limit_monthly' => 50,
    'accuracy_tier' => 'premium',
    'team_member_limit' => 1,
]
```

**Team Tier**:
```php
[
    'tier' => 'team',
    'price' => 39,
    'document_limit' => null,        // Unlimited
    'analysis_word_limit' => 10000,
    'daily_analysis_limit' => null,  // Unlimited
    'rewrite_limit_monthly' => null, // Unlimited
    'accuracy_tier' => 'premium',
    'team_member_limit' => 5,
]
```

## Usage Flow

### Document Creation & Analysis

```php
// 1. Check daily analysis limit
$tierLimitService->checkDailyAnalysisLimit($user);

// 2. Check document limit
$tierLimitService->checkDocumentLimit($user);

// 3. Check word count limit (for analysis)
$tierLimitService->checkAnalysisWordLimit($user, $content);

// 4. Create document
$document = Document::create([...]);

// 5. Track usage
$tierLimitService->trackDocumentCreation($user);
$tierLimitService->trackAnalysisUsage($user);
```

### Rewrite Request

```php
// 1. Check access (Pro/Team tier required)
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

// Daily analyses (for free tier)
$remaining = $tierLimitService->getRemainingAnalysesToday($user);
// Returns: int (e.g., 3) or null (unlimited)

// Rewrites (for pro tier)
$remaining = $tierLimitService->getRemainingRewrites($user);
// Returns: int (e.g., 35) or null (unlimited)
```

### Show Reset Dates

```php
$subscription = $user->subscription;

// Monthly reset
$monthlyResetDate = $subscription->usage_reset_at->addMonth()->format('M d, Y');
// Example: "Mar 13, 2026"

// Daily reset
$dailyResetTime = $subscription->analysis_reset_at->addDay()->format('h:i A');
// Example: "12:00 AM"
```

### UI Example

```blade
{{-- Daily Analysis Usage (Free Tier) --}}
<div class="stat">
    <div class="stat-title">Analyses Today</div>
    <div class="stat-value">
        {{ $subscription->analyses_used_today }} / 
        {{ $subscription->daily_analysis_limit ?? '∞' }}
    </div>
    <div class="stat-desc">
        Resets at midnight
    </div>
    <progress 
        class="progress progress-primary" 
        value="{{ $subscription->analyses_used_today }}" 
        max="{{ $subscription->daily_analysis_limit ?? 100 }}"
    ></progress>
</div>

{{-- Monthly Rewrite Usage (Pro Tier) --}}
<div class="stat">
    <div class="stat-title">Rewrites This Month</div>
    <div class="stat-value">
        {{ $subscription->rewrites_used_this_month }} / 
        {{ $subscription->rewrite_limit_monthly ?? '∞' }}
    </div>
    <div class="stat-desc">
        Resets {{ $subscription->usage_reset_at->addMonth()->format('M d, Y') }}
    </div>
    <progress 
        class="progress progress-primary" 
        value="{{ $subscription->rewrites_used_this_month }}" 
        max="{{ $subscription->rewrite_limit_monthly ?? 100 }}"
    ></progress>
</div>
```

## Subscription Creation

When a user signs up or upgrades:

```php
Subscription::create([
    'user_id' => $user->id,
    'tier' => 'free', // or 'pro' or 'team'
    'document_limit' => 10, // or null for pro/team
    'rewrite_limit_monthly' => 0, // 0 for free, 50 for pro, null for team
    'analysis_word_limit' => 800, // 800 for free, 10000 for pro/team
    'daily_analysis_limit' => 5, // 5 for free, null for pro/team
    'documents_used' => 0,
    'rewrites_used_this_month' => 0,
    'analyses_used_today' => 0,
    'usage_reset_at' => now(), // Start of billing period
    'analysis_reset_at' => now()->startOfDay(),
    'accuracy_tier' => 'standard', // or 'premium'
    'team_member_limit' => 1, // or 5 for team
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
    'analysis_word_limit' => 10000,
    'daily_analysis_limit' => null, // Unlimited
    'accuracy_tier' => 'premium',
    // Keep existing usage counts
]);
```

### Upgrade to Team

```php
$subscription->update([
    'tier' => 'team',
    'document_limit' => null, // Unlimited
    'rewrite_limit_monthly' => null, // Unlimited
    'analysis_word_limit' => 10000,
    'daily_analysis_limit' => null, // Unlimited
    'accuracy_tier' => 'premium',
    'team_member_limit' => 5,
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
    'daily_analysis_limit' => 5,
    'accuracy_tier' => 'standard',
    'team_member_limit' => 1,
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
7. **Multi-tier Support**: Easily handles Free/Pro/Team distinctions

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

test('subscription resets daily analysis usage automatically', function () {
    $subscription = Subscription::factory()->create([
        'analyses_used_today' => 5,
        'analysis_reset_at' => now()->subDay(),
    ]);
    
    $subscription->resetDailyAnalysisIfNeeded();
    
    expect($subscription->fresh()->analyses_used_today)->toBe(0);
});

test('tracks document creation usage', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'documents_used' => 5,
    ]);
    
    $tierLimitService->trackDocumentCreation($user);
    
    expect($subscription->fresh()->documents_used)->toBe(6);
});

test('enforces daily analysis limit for free tier', function () {
    $user = User::factory()->create(['tier' => 'free']);
    $subscription = Subscription::factory()->for($user)->create([
        'daily_analysis_limit' => 5,
        'analyses_used_today' => 5,
    ]);
    
    expect(fn() => $tierLimitService->checkDailyAnalysisLimit($user))
        ->toThrow(TierLimitException::class);
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
            'document_limit' => $user->tier === 'free' ? 10 : null,
            'rewrite_limit_monthly' => match($user->tier) {
                'free' => 0,
                'pro' => 50,
                'team' => null,
            },
            'analysis_word_limit' => $user->tier === 'free' ? 800 : 10000,
            'daily_analysis_limit' => $user->tier === 'free' ? 5 : null,
            'documents_used' => $user->documents()->count(),
            'rewrites_used_this_month' => 0, // Start fresh
            'analyses_used_today' => 0,
            'usage_reset_at' => now(),
            'analysis_reset_at' => now()->startOfDay(),
            'accuracy_tier' => $user->tier === 'free' ? 'standard' : 'premium',
            'team_member_limit' => $user->tier === 'team' ? 5 : 1,
            'starts_at' => $user->created_at,
            'ends_at' => null,
        ]);
    }
});
```
