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
        'doge_balance',
        'doge_withdrawn',
        'wallet_address',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'invested_amount' => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'withdrawn_amount' => 'decimal:2',
        'referral_earnings' => 'decimal:2',
        'doge_balance' => 'decimal:8',
        'doge_withdrawn' => 'decimal:8',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['balance', 'invested_amount', 'earned_amount', 'doge_balance', 'status'])
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

    public function deductBalance($amount, $description = null, $type = 'withdrawal')
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->save();

            // Create transaction record
            Transaction::create([
                'user_id' => $this->user_id,
                'transaction_id' => 'TXN_' . uniqid(),
                'type' => $type,
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

    public function addDogeBalance($amount, $description = null)
    {
        $this->doge_balance += $amount;
        $this->save();

        Transaction::create([
            'user_id' => $this->user_id,
            'transaction_id' => 'TXN_' . uniqid(),
            'type' => 'deposit',
            'currency' => 'DOGE',
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => $description ?? 'DOGE balance added',
            'processed_at' => now(),
        ]);
    }

    public function deductDogeBalance($amount, $description = null)
    {
        if ($this->doge_balance >= $amount) {
            $this->doge_balance -= $amount;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Check if user qualifies for DOGE bonus and award it (one-time).
     */
    public function checkAndAwardDogeBonus()
    {
        $threshold = WithdrawalSetting::getDogeBonusThreshold();
        $bonusAmount = WithdrawalSetting::getDogeBonusAmount();

        if ($bonusAmount <= 0 || $threshold <= 0) {
            return false;
        }

        $totalEarnings = $this->earned_amount + $this->referral_earnings;

        if ($totalEarnings < $threshold) {
            return false;
        }

        // Check if user already received the DOGE bonus
        $alreadyAwarded = Transaction::where('user_id', $this->user_id)
            ->where('type', 'doge_bonus')
            ->exists();

        if ($alreadyAwarded) {
            return false;
        }

        // Award the DOGE bonus
        $this->doge_balance += $bonusAmount;
        $this->save();

        Transaction::create([
            'user_id' => $this->user_id,
            'transaction_id' => 'DOGE_' . uniqid(),
            'type' => 'doge_bonus',
            'currency' => 'DOGE',
            'amount' => $bonusAmount,
            'net_amount' => $bonusAmount,
            'status' => 'completed',
            'description' => "DOGE bonus awarded for reaching ${threshold} USDT in total earnings",
            'processed_at' => now(),
        ]);

        return true;
    }

    public function getTotalEarningsAttribute()
    {
        return $this->earned_amount + $this->referral_earnings;
    }
}
