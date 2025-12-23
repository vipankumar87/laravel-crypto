<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReferralBonus extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'referrer_id',
        'investment_id',
        'level',
        'amount',
        'investment_amount',
        'bonus_percentage',
        'type',
        'status',
        'description',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'investment_amount' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'level', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    // Scopes
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForReferrer($query, $referrerId)
    {
        return $query->where('referrer_id', $referrerId);
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    public static function getTotalByLevel($userId, $level)
    {
        return static::where('referrer_id', $userId)
            ->where('level', $level)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public static function getTotalEarnings($userId)
    {
        return static::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public static function getEarningsByLevel($userId)
    {
        return static::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->selectRaw('level, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('level')
            ->orderBy('level')
            ->get();
    }
}
