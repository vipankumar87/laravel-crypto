# Daily Bonus Command Documentation

## Overview
The `app:update-daily-bonus` command processes daily bonuses for user earning wallets, handling both self (direct) earnings from investments and referral earnings.

## Features

### 1. Duplicate Prevention
- **Database-based tracking**: Uses `daily_bonus_logs` table to track processing dates
- **Unique date constraint**: Prevents multiple processing on the same day
- **Force option**: `--force` flag allows overriding the duplicate check

### 2. Database Locking
- **Row-level locking**: Uses `lockForUpdate()` to prevent race conditions
- **Transaction safety**: All operations wrapped in database transactions
- **Double-check logic**: Verifies investment processing status before updates

### 3. Comprehensive Logging
- **Processing history**: Stores detailed records in `daily_bonus_logs` table
- **Audit trail**: Logs all processing activities with timestamps
- **Error handling**: Comprehensive error logging with stack traces

## Usage

### Basic Usage
```bash
php artisan app:update-daily-bonus
```

### Force Processing (skip duplicate check)
```bash
php artisan app:update-daily-bonus --force
```

### View Processing History
```bash
php artisan app:update-daily-bonus --history
```

### Help
```bash
php artisan app:update-daily-bonus --help
```

## Database Schema

### daily_bonus_logs table
- `id` - Primary key
- `process_date` - Unique date field (prevents duplicates)
- `total_self_earnings` - Direct earnings from investments
- `total_referral_earnings` - Earnings from referrals
- `total_earnings` - Combined total earnings
- `processed_investments` - Number of investments processed
- `processed_at` - Processing timestamp
- `notes` - Additional notes (e.g., force processing)
- `created_at`, `updated_at` - Laravel timestamps

## Processing Logic

### Self (Direct) Earnings
1. **Query**: Selects active investments not processed today
2. **Validation**: Double-checks `last_earning_date` to prevent duplicates
3. **Calculation**: Uses `calculateDailyEarning()` method from Investment model
4. **Updates**: 
   - Updates investment `earned_amount` and `last_earning_date`
   - Updates wallet `earned_amount` and `balance`
   - Creates transaction record
5. **Zero Handling**: Updates `last_earning_date` even for zero earnings

### Referral Earnings
- Currently processed when new investments are made
- Framework ready for additional ongoing referral logic

### Transaction Creation
- **Duplicate prevention**: Checks for existing transactions for same investment/date
- **Unique IDs**: Generates unique transaction IDs with timestamp
- **Investment linking**: Links transactions to investments via description

## Safety Features

### 1. Race Condition Prevention
```php
// Uses row-level locking
Investment::where(...)->lockForUpdate()->get();
Wallet::where(...)->lockForUpdate()->first();
```

### 2. Atomic Operations
```php
DB::transaction(function () {
    // All database operations here
});
```

### 3. Duplicate Detection
```php
// Multiple layers of duplicate prevention
if ($investment->last_earning_date->format('Y-m-d') === $today) {
    continue; // Skip if already processed
}
```

## Scheduling

The command is scheduled to run daily at 12:01 AM in `bootstrap/app.php`:

```php
$schedule->command('app:update-daily-bonus')
    ->daily()
    ->at('00:01')
    ->withoutOverlapping()
    ->runInBackground();
```

## Error Handling

### Database Errors
- Transaction rollback on failure
- Detailed error logging
- Graceful error messages

### Missing Data
- Wallet creation for users without wallets
- Skip processing for missing wallets with error logging
- Continue processing other investments on individual failures

## Monitoring

### History Command
The `--history` option shows:
- Last 30 days of processing records
- Earnings breakdown by type
- Number of investments processed
- Processing timestamps
- Any notes (e.g., force processing)

### Logging
- File logging for all operations
- Structured log data for monitoring
- Error tracking with stack traces

## Performance Considerations

### Efficient Queries
- Uses date-based filtering for performance
- Limits to active investments only
- Row-level locking only when necessary

### Batch Processing
- Processes all eligible investments in single transaction
- Minimizes database round trips

### Memory Usage
- Streams results to prevent memory issues
- Efficient data structures for processing

## Future Enhancements

### Potential Improvements
1. **Chunked Processing**: Process investments in batches for very large datasets
2. **Retry Logic**: Automatic retry for failed investments
3. **Notifications**: Alert system for processing failures
4. **Analytics Dashboard**: Real-time monitoring interface
5. **Manual Override**: Web interface for manual processing

### Scalability
- Designed to handle thousands of investments
- Database constraints prevent data corruption
- Logging supports troubleshooting at scale

## Troubleshooting

### Common Issues
1. **"Already processed today"**: Use `--force` if manual reprocessing is needed
2. **Database connection errors**: Check database configuration
3. **Missing wallets**: Command auto-creates missing wallets
4. **Transaction duplicates**: Built-in duplicate prevention handles this

### Debug Mode
```bash
php artisan app:update-daily-bonus -v
```

### Log Location
Check Laravel logs: `storage/logs/laravel.log`
