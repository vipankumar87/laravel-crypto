<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Investment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'investment_plan_id',
        'investment_plan',
        'amount',
        'platform_fee',
        'transaction_fee',
        'total_fees',
        'gross_amount',
        'expected_return',
        'earned_amount',
        'duration_days',
        'monthly_return_rate',
        'daily_return_rate',
        'status',
        'payment_method',
        'start_date',
        'end_date',
        'last_earning_date',
        'monthly_bonus_earned',
        'last_monthly_bonus_date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'investment_plan_id' => 'integer',
        'amount' => 'decimal:4',
        'platform_fee' => 'decimal:4',
        'transaction_fee' => 'decimal:4',
        'total_fees' => 'decimal:4',
        'gross_amount' => 'decimal:4',
        'expected_return' => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'monthly_return_rate' => 'decimal:2',
        'daily_return_rate' => 'decimal:2',
        'duration_days' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_earning_date' => 'datetime',
        'monthly_bonus_earned' => 'decimal:2',
        'last_monthly_bonus_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'earned_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class);
    }

    public function referralBonuses()
    {
        return $this->hasMany(ReferralBonus::class);
    }

    // Helper methods

    /**
     * Get the effective daily rate for the current month.
     * daily rate = monthly_return_rate / days_in_current_month
     */
    public function getEffectiveDailyRate(): float
    {
        if ($this->monthly_return_rate > 0) {
            return (float) $this->monthly_return_rate / Carbon::now()->daysInMonth;
        }
        // Fallback to stored daily_return_rate for legacy investments
        return (float) $this->daily_return_rate;
    }

    public function calculateDailyEarning()
    {
        return $this->amount * ($this->getEffectiveDailyRate() / 100);
    }

    public function isActive()
    {
        return $this->status === 'active' && now() >= $this->start_date && now() <= $this->end_date;
    }

    public function canEarn()
    {
        return $this->isActive() &&
               ($this->last_earning_date === null ||
                $this->last_earning_date < now()->startOfDay());
    }

    public function calculateEarningForInterval(int $intervalsPerDay)
    {
        $dailyEarning = $this->calculateDailyEarning();
        if ($intervalsPerDay <= 0) {
            $intervalsPerDay = 1;
        }
        return $dailyEarning / $intervalsPerDay;
    }
}
