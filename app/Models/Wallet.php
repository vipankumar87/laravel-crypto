<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Wallet extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'balance',
        'invested_amount',
        'earned_amount',
        'withdrawn_amount',
        'referral_earnings',
        'wallet_address',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'invested_amount' => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'withdrawn_amount' => 'decimal:2',
        'referral_earnings' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['balance', 'invested_amount', 'earned_amount', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function addBalance($amount, $description = null)
    {
        $this->balance += $amount;
        $this->save();

        // Create transaction record
        Transaction::create([
            'user_id' => $this->user_id,
            'transaction_id' => 'TXN_' . uniqid(),
            'type' => 'deposit',
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => $description ?? 'Balance added',
            'processed_at' => now(),
        ]);
    }

    public function deductBalance($amount, $description = null)
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->save();

            // Create transaction record
            Transaction::create([
                'user_id' => $this->user_id,
                'transaction_id' => 'TXN_' . uniqid(),
                'type' => 'withdrawal',
                'amount' => $amount,
                'net_amount' => $amount,
                'status' => 'completed',
                'description' => $description ?? 'Balance deducted',
                'processed_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    public function getTotalEarningsAttribute()
    {
        return $this->earned_amount + $this->referral_earnings;
    }
}
