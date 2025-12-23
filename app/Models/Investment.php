<?php

namespace App\Models;

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
        'expected_return',
        'earned_amount',
        'duration_days',
        'daily_return_rate',
        'status',
        'payment_method',
        'start_date',
        'end_date',
        'last_earning_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'daily_return_rate' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_earning_date' => 'datetime',
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

    // Helper methods
    public function calculateDailyEarning()
    {
        return $this->amount * ($this->daily_return_rate / 100);
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
}
