<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReferralLevelSetting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'level',
        'percentage',
        'is_active',
        'description',
    ];

    protected $casts = [
        'level' => 'integer',
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['level', 'percentage', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level', 'asc');
    }

    // Static helpers
    public static function getActiveLevels()
    {
        return static::active()->ordered()->get();
    }

    public static function getLevelPercentages()
    {
        return static::active()
            ->ordered()
            ->pluck('percentage', 'level')
            ->toArray();
    }

    public static function getMaxLevel()
    {
        return static::active()->max('level') ?? 5;
    }

    public static function getPercentageForLevel($level)
    {
        $setting = static::where('level', $level)->where('is_active', true)->first();
        return $setting ? (float) $setting->percentage : 0;
    }
}
