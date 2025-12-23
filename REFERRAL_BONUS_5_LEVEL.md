# 5-Level Referral Bonus System

## Overview
This implementation provides a comprehensive 5-level referral bonus system that automatically distributes bonuses to upline referrers when users make investments. The system tracks all bonuses in a dedicated table and provides detailed analytics.

## System Architecture

### Database Structure

#### `referral_bonuses` Table
Tracks all referral bonuses distributed across 5 levels.

**Columns:**
- `id` - Primary key
- `user_id` - The user who made the investment (triggered the bonus)
- `referrer_id` - The user receiving the bonus
- `investment_id` - The investment that triggered the bonus
- `level` - Referral level (1-5)
- `amount` - Bonus amount earned
- `investment_amount` - Original investment amount
- `bonus_percentage` - Percentage rate for this level
- `type` - Type of bonus (investment, deposit, etc.)
- `status` - Status (pending, completed, cancelled)
- `description` - Description of the bonus
- `processed_at` - When the bonus was processed
- `created_at` / `updated_at` - Timestamps

### Bonus Percentage Structure

**Default 5-Level Rates:**
- **Level 1:** 10% (Direct referrals)
- **Level 2:** 5% (Referrals of your referrals)
- **Level 3:** 3% (Third level down)
- **Level 4:** 2% (Fourth level down)
- **Level 5:** 1% (Fifth level down)

**Total Potential:** 21% of investment amount distributed across upline

### Example Calculation

If User E invests $1,000:
- User D (Level 1 - Direct referrer): $100 (10%)
- User C (Level 2): $50 (5%)
- User B (Level 3): $30 (3%)
- User A (Level 4): $20 (2%)
- User Root (Level 5): $10 (1%)

**Total Distributed:** $210 (21% of $1,000)

## Components Implemented

### 1. Database Migration
**File:** `database/migrations/2025_12_21_120000_create_referral_bonuses_table.php`

Creates the `referral_bonuses` table with proper indexes for performance.

### 2. ReferralBonus Model
**File:** `app/Models/ReferralBonus.php`

**Features:**
- Relationships to User and Investment models
- Scopes for filtering by level, status, referrer
- Helper methods for calculations
- Activity logging integration

**Key Methods:**
- `getTotalByLevel($userId, $level)` - Get total earnings for a specific level
- `getTotalEarnings($userId)` - Get total referral earnings
- `getEarningsByLevel($userId)` - Get breakdown by level

### 3. User Model Updates
**File:** `app/Models/User.php`

**New Relationships:**
- `referralBonuses()` - Bonuses earned by this user
- `receivedBonuses()` - Bonuses from this user's investments

**New Methods:**
- `getUplineChain($maxLevels = 5)` - Get upline referrers up to 5 levels
- `getReferralsByLevel($level)` - Get referrals at specific level
- `getAllDownlineReferrals($maxLevels = 5)` - Get all downline referrals
- `getTotalReferralEarnings()` - Get total referral earnings
- `getReferralEarningsByLevel()` - Get earnings breakdown by level
- `getReferralCountByLevel($maxLevels = 5)` - Count referrals per level

### 4. ReferralService
**File:** `app/Services/ReferralService.php`

Central service for handling all referral bonus operations.

**Key Methods:**

#### `distributeInvestmentBonuses(User $user, Investment $investment)`
Automatically distributes bonuses across 5 levels when investment is made.

**Process:**
1. Gets upline chain (up to 5 levels)
2. For each level:
   - Calculates bonus amount based on percentage
   - Creates ReferralBonus record
   - Adds bonus to referrer's wallet
   - Updates referral_earnings in wallet
   - Logs the transaction

**Returns:**
```php
[
    'success' => true,
    'bonuses_distributed' => [
        ['level' => 1, 'referrer' => 'John', 'amount' => 100, 'percentage' => 10],
        // ... more levels
    ],
    'total_distributed' => 210
]
```

#### `getReferralStats(User $user)`
Gets comprehensive referral statistics.

**Returns:**
```php
[
    'total_earnings' => 1500.00,
    'earnings_by_level' => [
        1 => ['total' => 800, 'count' => 8, 'percentage' => 10],
        2 => ['total' => 400, 'count' => 8, 'percentage' => 5],
        // ... levels 3-5
    ],
    'referrals_by_level' => [
        1 => 10,  // 10 direct referrals
        2 => 25,  // 25 level-2 referrals
        // ... levels 3-5
    ],
    'total_referrals' => 75
]
```

#### Other Methods:
- `getReferralTree($user, $maxLevels = 5)` - Get detailed referral tree
- `calculatePotentialEarnings($investmentAmount)` - Calculate potential earnings
- `getRecentBonuses($user, $limit = 10)` - Get recent bonuses
- `getBonusSummaryByPeriod($user, $period)` - Get bonuses by time period

### 5. InvestmentController Updates
**File:** `app/Http/Controllers/InvestmentController.php`

**Changes:**
- Injected `ReferralService` dependency
- Replaced single-level bonus with 5-level distribution
- Added logging for bonus distribution

**Integration Point:**
```php
// Distribute 5-level referral bonuses
if ($user->referred_by) {
    $bonusResult = $this->referralService->distributeInvestmentBonuses($user, $investment);
    
    if ($bonusResult['success']) {
        \Log::info('5-level referral bonuses distributed', [
            'user_id' => $user->id,
            'investment_id' => $investment->id,
            'total_distributed' => $bonusResult['total_distributed'],
            'levels' => count($bonusResult['bonuses_distributed']),
        ]);
    }
}
```

### 6. ReferralController Updates
**File:** `app/Http/Controllers/ReferralController.php`

**Changes:**
- Injected `ReferralService` dependency
- Updated `index()` to show 5-level statistics
- Updated `earnings()` to display detailed bonus breakdown

### 7. Referral Earnings View
**File:** `resources/views/referrals/earnings.blade.php`

**Features:**

#### 5-Level Bonus Breakdown Table
Shows comprehensive breakdown of earnings by level:
- Level number with badge
- Bonus rate percentage
- Total earned per level
- Number of bonuses received
- Active/inactive status

#### Level Explanation Section
Educational content explaining how each level works.

#### Detailed Bonus History Table
Shows individual bonus transactions with:
- Level badge (color-coded)
- User who made investment
- Investment amount
- Bonus rate applied
- Bonus amount earned
- Date and time
- Status

**Color Coding:**
- Level 1: Green (Success)
- Level 2: Blue (Info)
- Level 3: Yellow (Warning)
- Level 4: Primary
- Level 5: Secondary

## How It Works

### When a User Makes an Investment:

1. **Investment Created**
   - User invests $1,000 in a plan
   - Investment record created with status 'active'

2. **Upline Chain Retrieved**
   - System finds user's referrer (Level 1)
   - Finds referrer's referrer (Level 2)
   - Continues up to 5 levels

3. **Bonuses Calculated & Distributed**
   - For each level in upline chain:
     - Calculate bonus: `investment_amount × level_percentage / 100`
     - Create `ReferralBonus` record
     - Add bonus to referrer's wallet balance
     - Update referrer's `referral_earnings` counter
     - Create transaction record

4. **Logging & Tracking**
   - All bonuses logged for audit trail
   - Activity log tracks changes
   - Database maintains complete history

### Viewing Bonuses

Users can view their referral bonuses at:
- `/referrals/earnings` - Detailed 5-level breakdown

**Information Displayed:**
- Total earnings across all levels
- Earnings breakdown by level (1-5)
- Number of bonuses per level
- Detailed transaction history
- Level explanation guide

## Database Queries

### Get User's Total Referral Earnings
```php
$totalEarnings = ReferralBonus::where('referrer_id', $userId)
    ->where('status', 'completed')
    ->sum('amount');
```

### Get Earnings by Level
```php
$earningsByLevel = ReferralBonus::where('referrer_id', $userId)
    ->where('status', 'completed')
    ->selectRaw('level, SUM(amount) as total, COUNT(*) as count')
    ->groupBy('level')
    ->get();
```

### Get Recent Bonuses
```php
$recentBonuses = ReferralBonus::where('referrer_id', $userId)
    ->with(['user', 'investment'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

## Configuration

### Customizing Bonus Percentages

To change the default percentages, modify `ReferralService`:

```php
protected $levelPercentages = [
    1 => 12.0,  // Change Level 1 to 12%
    2 => 6.0,   // Change Level 2 to 6%
    3 => 4.0,   // Change Level 3 to 4%
    4 => 2.5,   // Change Level 4 to 2.5%
    5 => 1.5,   // Change Level 5 to 1.5%
];
```

Or create a config file at `config/referral.php`:

```php
return [
    'level_percentages' => [
        1 => 10.0,
        2 => 5.0,
        3 => 3.0,
        4 => 2.0,
        5 => 1.0,
    ],
];
```

## Migration Instructions

### 1. Run the Migration
```bash
php artisan migrate
```

This creates the `referral_bonuses` table.

### 2. Test the System

**Create Test Users:**
```php
// Create a referral chain
$userA = User::create([...]);
$userB = User::create(['referred_by' => $userA->id, ...]);
$userC = User::create(['referred_by' => $userB->id, ...]);
$userD = User::create(['referred_by' => $userC->id, ...]);
$userE = User::create(['referred_by' => $userD->id, ...]);
```

**Make Test Investment:**
```php
// User E makes investment
$investment = Investment::create([
    'user_id' => $userE->id,
    'amount' => 1000,
    // ... other fields
]);

// Bonuses automatically distributed to A, B, C, D
```

**Verify Bonuses:**
```php
// Check User D's earnings (Level 1)
$userD->referralBonuses; // Should show $100 bonus

// Check User A's earnings (Level 4)
$userA->referralBonuses; // Should show $20 bonus
```

## API Endpoints

While not explicitly created, you can add these routes for API access:

```php
Route::get('/api/referral/stats', function() {
    $user = Auth::user();
    $service = new ReferralService();
    return $service->getReferralStats($user);
});

Route::get('/api/referral/bonuses', function() {
    $user = Auth::user();
    return $user->referralBonuses()
        ->with(['user', 'investment'])
        ->paginate(20);
});
```

## Security Considerations

1. **Transaction Safety**
   - All bonus distributions wrapped in database transactions
   - Rollback on any failure
   - Prevents partial distributions

2. **Validation**
   - Checks for wallet existence before distribution
   - Validates referrer relationships
   - Prevents circular references

3. **Logging**
   - All distributions logged
   - Activity log tracks changes
   - Audit trail maintained

## Performance Optimization

1. **Database Indexes**
   - Index on `user_id` and `created_at`
   - Index on `referrer_id` and `level`
   - Index on `investment_id`

2. **Eager Loading**
   - Use `with(['user', 'investment'])` to prevent N+1 queries
   - Load relationships when displaying lists

3. **Caching** (Optional)
   - Cache referral statistics
   - Cache upline chains
   - Invalidate on new investment

## Troubleshooting

### Bonuses Not Distributing

**Check:**
1. User has `referred_by` set
2. Referrer has wallet created
3. Database transaction not failing
4. Check logs for errors

### Incorrect Amounts

**Verify:**
1. Percentage configuration in `ReferralService`
2. Investment amount is correct
3. Calculation logic: `amount × percentage / 100`

### Missing Bonuses in View

**Ensure:**
1. Migration has been run
2. Relationships defined correctly
3. Controller passing correct data to view
4. User has actual bonuses in database

## Future Enhancements

1. **Bonus Caps**
   - Maximum bonus per level
   - Daily/monthly earning limits

2. **Conditional Bonuses**
   - Require minimum investment
   - Require active status

3. **Bonus Tiers**
   - Different rates based on user rank
   - Performance-based percentages

4. **Withdrawal Limits**
   - Minimum balance for withdrawal
   - Withdrawal fees

5. **Bonus Expiration**
   - Time-limited bonuses
   - Inactive account penalties

## Summary

The 5-level referral bonus system provides:
- ✅ Automatic bonus distribution across 5 levels
- ✅ Comprehensive tracking in dedicated table
- ✅ Detailed analytics and reporting
- ✅ User-friendly interface with breakdown
- ✅ Transaction safety and logging
- ✅ Configurable percentage rates
- ✅ Complete audit trail
- ✅ Performance-optimized queries

Users can now earn passive income from their entire downline network up to 5 levels deep, creating a powerful incentive for referrals and network growth.
