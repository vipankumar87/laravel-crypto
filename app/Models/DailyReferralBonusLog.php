<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyReferralBonusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "process_date",
        "processed_investments",
        "bonuses_distributed",
        "total_amount",
        "processed_at",
        "notes",
    ];

    protected $casts = [
        "process_date" => "date",
        "processed_investments" => "integer",
        "bonuses_distributed" => "integer",
        "total_amount" => "decimal:2",
        "processed_at" => "datetime",
    ];

    public static function hasBeenProcessed($date)
    {
        return static::where("process_date", $date)->exists();
    }

    public static function getProcessingRecord($date)
    {
        return static::where("process_date", $date)->first();
    }

    public static function createOrUpdateRecord($date, $processedInvestments, $bonusesDistributed, $totalAmount, $notes = null)
    {
        return static::updateOrCreate(
            ["process_date" => $date],
            [
                "processed_investments" => $processedInvestments,
                "bonuses_distributed" => $bonusesDistributed,
                "total_amount" => $totalAmount,
                "processed_at" => now(),
                "notes" => $notes,
            ]
        );
    }

    public static function getRecentHistory($days = 30)
    {
        return static::orderBy("process_date", "desc")
            ->limit($days)
            ->get();
    }
}
