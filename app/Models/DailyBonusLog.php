<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyBonusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_date',
        'total_self_earnings',
        'total_referral_earnings',
        'total_earnings',
        'processed_investments',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'process_date' => 'date',
        'total_self_earnings' => 'decimal:2',
        'total_referral_earnings' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'processed_investments' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * Check if daily bonuses have been processed for a specific date
     */
    public static function hasBeenProcessed($date)
    {
        return static::where('process_date', $date)->exists();
    }

    /**
     * Get the processing record for a specific date
     */
    public static function getProcessingRecord($date)
    {
        return static::where('process_date', $date)->first();
    }

    /**
     * Create or update processing record for a date
     */
    public static function createOrUpdateRecord($date, $selfEarnings, $referralEarnings, $processedInvestments, $notes = null)
    {
        return static::updateOrCreate(
            ['process_date' => $date],
            [
                'total_self_earnings' => $selfEarnings,
                'total_referral_earnings' => $referralEarnings,
                'total_earnings' => $selfEarnings + $referralEarnings,
                'processed_investments' => $processedInvestments,
                'processed_at' => now(),
                'notes' => $notes,
            ]
        );
    }

    /**
     * Get recent processing history
     */
    public static function getRecentHistory($days = 30)
    {
        return static::orderBy('process_date', 'desc')
            ->limit($days)
            ->get();
    }
}
