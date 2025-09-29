<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'daily_return_rate',
        'duration_days',
        'total_return_rate',
        'status',
        'max_investors',
        'referral_bonus_rate',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'daily_return_rate' => 'decimal:2',
        'total_return_rate' => 'decimal:2',
        'referral_bonus_rate' => 'decimal:2',
    ];

    // Relationships
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function getTotalInvestorsAttribute()
    {
        return $this->investments()->distinct('user_id')->count();
    }

    public function getTotalInvestedAttribute()
    {
        return $this->investments()->where('status', 'active')->sum('amount');
    }

    public function canAcceptNewInvestors()
    {
        return $this->status === 'active' &&
               ($this->max_investors === null || $this->total_investors < $this->max_investors);
    }
}
