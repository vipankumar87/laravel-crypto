# Referral Dashboard Implementation

## Overview
This implementation provides a comprehensive referral dashboard with information blocks showing user statistics and a hierarchical table displaying referral team structure across multiple levels.

## Features Implemented

### 1. Information Blocks (Top Section)

Four gradient-styled information cards displaying:

#### Wallet Balance
- **Icon:** Wallet icon
- **Color:** Purple gradient (667eea → 764ba2)
- **Data:** Current wallet balance in USDT
- **Format:** Displays up to 4 decimal places

#### Mining (Interest Earned)
- **Icon:** Chart line icon
- **Color:** Pink gradient (f093fb → f5576c)
- **Data:** Total interest earned from investments
- **Format:** Displays up to 10 decimal places

#### Total Investment
- **Icon:** Coins icon
- **Color:** Blue gradient (4facfe → 00f2fe)
- **Data:** Total amount invested by user
- **Format:** Displays 2 decimal places

#### Total Affiliate Bonus
- **Icon:** Users icon
- **Color:** Orange-yellow gradient (fa709a → fee140)
- **Data:** Total referral earnings from all levels
- **Format:** Displays up to 10 decimal places

### 2. Referral URL Section

- **Design:** Dark gradient card (2d3561 → 1a1f3a)
- **Features:**
  - Read-only input field with referral URL
  - Copy button with visual feedback
  - Changes to "Copied!" with green color on click
  - Auto-reverts after 2 seconds

### 3. Hierarchical Referral Table

Main table showing team structure across 6 levels:

#### Columns:
1. **S.No** - Serial number
2. **Level** - Level badge (color-coded by level)
3. **Team Size** - Number of referrals at this level
4. **Total Invest** - Sum of all investments at this level
5. **Avg Bonus** - Average bonus earned from this level
6. **Action** - "View Details" button (enabled if team size > 0)

#### Level Color Coding:
- **Level 1:** Green (Success) - Direct referrals
- **Level 2:** Blue (Info)
- **Level 3:** Yellow (Warning)
- **Level 4:** Primary
- **Level 5:** Secondary
- **Level 6:** Dark

### 4. Level Details Page

When clicking "View Details" for a specific level, users see:

#### Summary Section
- Same level row from main table
- Shows aggregated stats for that level

#### Detailed Referrals Table

Columns:
1. **S.No** - Serial number
2. **Sponsor** - Who referred this user
3. **UserName** - Username of the referral
4. **Email** - Email address
5. **Total Invest** - Total investment by this user
6. **Average Monthly Income** - Interest earned in last 30 days

#### Statistics Cards (Bottom)
- **Total Members:** Count of referrals at this level
- **Total Investment:** Sum of all investments
- **Average Investment:** Average per member
- **Total Monthly Income:** Sum of monthly income

## File Structure

### Controller
**File:** `app/Http/Controllers/ReferralController.php`

#### `index()` Method
Calculates and provides:
- Wallet balance, interest, investment, affiliate bonus
- Referral URL
- Level statistics (1-6) with team size, total investment, average bonus

#### `levelDetails($level)` Method
Provides detailed information for a specific level:
- Individual referral data
- Sponsor information
- Monthly income calculations
- Investment totals

### Views

#### Main Dashboard
**File:** `resources/views/referrals/dashboard.blade.php`

Features:
- 4 gradient info blocks
- Referral URL section with copy functionality
- Hierarchical table (6 levels)
- Responsive design
- Dark theme with gradients

#### Level Details
**File:** `resources/views/referrals/level-details.blade.php`

Features:
- Back button to dashboard
- Referral URL section
- Level summary table
- Detailed referrals table
- Statistics summary cards

### Routes
**File:** `routes/web.php`

```php
Route::prefix('referrals')->name('referrals.')->group(function () {
    Route::get('/', [ReferralController::class, 'index'])->name('index');
    Route::get('/level/{level}', [ReferralController::class, 'levelDetails'])->name('level-details');
    Route::get('/tree', [ReferralController::class, 'tree'])->name('tree');
    Route::get('/earnings', [ReferralController::class, 'earnings'])->name('earnings');
});
```

## Data Calculations

### Wallet Balance
```php
$walletBalance = $user->wallet ? $user->wallet->balance : 0;
```

### Total Interest (Mining)
```php
$totalInterest = $user->wallet ? $user->wallet->earned_amount : 0;
```

### Total Investment
```php
$totalInvestment = $user->wallet ? $user->wallet->invested_amount : 0;
```

### Total Affiliate Bonus
```php
$totalAffiliateBonus = $user->wallet ? $user->wallet->referral_earnings : 0;
```

### Level Statistics
For each level (1-6):

**Team Size:**
```php
$teamSize = $user->getReferralsByLevel($level)->count();
```

**Total Investment:**
```php
$totalInvest = 0;
foreach ($levelReferrals as $referral) {
    if ($referral->wallet) {
        $totalInvest += $referral->wallet->invested_amount;
    }
}
```

**Average Bonus:**
```php
$avgBonus = ReferralBonus::where('referrer_id', $user->id)
    ->where('level', $level)
    ->where('status', 'completed')
    ->avg('amount') ?? 0;
```

### Monthly Income Calculation
```php
$monthlyIncome = Transaction::where('user_id', $referral->id)
    ->where('type', 'interest')
    ->where('created_at', '>=', now()->subDays(30))
    ->sum('amount');
```

## Design Specifications

### Color Scheme

**Info Blocks:**
- Wallet Balance: Purple gradient (#667eea → #764ba2)
- Mining: Pink gradient (#f093fb → #f5576c)
- Total Investment: Blue gradient (#4facfe → #00f2fe)
- Total Affiliate Bonus: Orange-yellow gradient (#fa709a → #fee140)

**Tables:**
- Background: Dark gradient (#2d3561 → #1a1f3a)
- Text: White
- Borders: rgba(255,255,255,0.1)
- Header: rgba(0,0,0,0.3)

**Level Badges:**
- Level 1: badge-success (Green)
- Level 2: badge-info (Blue)
- Level 3: badge-warning (Yellow)
- Level 4: badge-primary
- Level 5: badge-secondary
- Level 6: badge-dark

### Responsive Design
- Uses Bootstrap grid system
- Info blocks: col-lg-3 col-md-6 (4 columns on large, 2 on medium)
- Tables: Responsive with horizontal scroll on mobile
- Cards: Full width with proper spacing

## JavaScript Functionality

### Copy Referral URL
```javascript
function copyReferralUrl() {
    const urlInput = document.getElementById('referralUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999);
    
    document.execCommand('copy');
    
    // Visual feedback
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
```

## Usage

### Accessing the Dashboard
Navigate to: `/referrals`

**Requirements:**
- User must be logged in
- User must have made at least one investment

### Viewing Level Details
Click "View Details" button for any level with team members.

**URL Format:** `/referrals/level/{level}`

Example: `/referrals/level/1` for Level 1 details

## Data Flow

### Main Dashboard
1. User accesses `/referrals`
2. Controller checks if user has invested
3. Calculates wallet stats (balance, interest, investment, bonus)
4. Loops through levels 1-6 to get team statistics
5. Returns data to view
6. View renders info blocks and hierarchical table

### Level Details
1. User clicks "View Details" for a level
2. Controller retrieves referrals at that specific level
3. For each referral:
   - Gets sponsor (referrer)
   - Calculates monthly income (last 30 days)
   - Gets investment total
4. Returns detailed data to view
5. View renders summary and detailed tables

## Database Queries

### Get Referrals by Level
Uses recursive method from User model:
```php
public function getReferralsByLevel($level = 1)
{
    if ($level === 1) {
        return $this->referrals;
    }

    $referrals = collect();
    $previousLevel = $this->getReferralsByLevel($level - 1);

    foreach ($previousLevel as $user) {
        $referrals = $referrals->merge($user->referrals);
    }

    return $referrals;
}
```

### Get Average Bonus by Level
```php
ReferralBonus::where('referrer_id', $user->id)
    ->where('level', $level)
    ->where('status', 'completed')
    ->avg('amount');
```

### Get Monthly Income
```php
Transaction::where('user_id', $referral->id)
    ->where('type', 'interest')
    ->where('created_at', '>=', now()->subDays(30))
    ->sum('amount');
```

## Performance Considerations

### Optimization Tips

1. **Eager Loading**
   ```php
   $levelReferrals = $user->getReferralsByLevel($level)->load('wallet');
   ```

2. **Caching Level Stats**
   ```php
   Cache::remember("user_{$user->id}_level_stats", 3600, function() use ($user) {
       // Calculate level stats
   });
   ```

3. **Pagination for Large Teams**
   For level details with many referrals:
   ```php
   $referrals = $user->getReferralsByLevel($level)->paginate(50);
   ```

## Customization

### Changing Number of Levels
To show more or fewer levels, modify the loop in `ReferralController@index`:

```php
// Change from 6 to desired number
for ($level = 1; $level <= 6; $level++) {
    // ...
}
```

### Modifying Info Block Colors
Update gradient styles in `dashboard.blade.php`:

```html
<div class="card text-white" style="background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);">
```

### Customizing Table Appearance
Modify the table styles in both view files:

```html
<table class="table table-hover mb-0" style="color: white;">
```

## Troubleshooting

### Info Blocks Showing Zero
**Check:**
1. User has a wallet created
2. Wallet fields are populated correctly
3. Database migrations have run

### Level Table Empty
**Verify:**
1. User has made an investment (required for referral link)
2. Referrals exist in database
3. `referred_by` field is set correctly

### "View Details" Button Disabled
**Reason:** Team size is 0 for that level
**Solution:** Build referral network at that level

### Monthly Income Not Calculating
**Check:**
1. Transactions table has `type` = 'interest'
2. `created_at` timestamps are correct
3. User has active investments generating interest

## Future Enhancements

1. **Real-time Updates**
   - WebSocket integration for live stats
   - Auto-refresh every 30 seconds

2. **Export Functionality**
   - Download level details as CSV/PDF
   - Generate team reports

3. **Filtering & Search**
   - Search referrals by username/email
   - Filter by investment amount
   - Date range filters

4. **Charts & Visualizations**
   - Team growth chart
   - Investment distribution pie chart
   - Monthly income trend line

5. **Notifications**
   - Alert when new referral joins
   - Notify when team member invests
   - Milestone achievements

## Summary

The referral dashboard provides:
- ✅ 4 gradient info blocks with key statistics
- ✅ Hierarchical table showing 6 levels of referrals
- ✅ Team size, investment totals, and average bonuses
- ✅ Detailed level view with individual referral data
- ✅ Sponsor tracking and monthly income calculations
- ✅ Copy-to-clipboard referral URL functionality
- ✅ Responsive design with dark theme
- ✅ Color-coded level badges
- ✅ Statistics summary cards

Users can now easily track their entire referral network, view team performance, and monitor earnings across all levels in a beautiful, intuitive interface.
