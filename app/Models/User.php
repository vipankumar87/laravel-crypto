<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Hash;
use App\Models\Wallet;
use App\Models\Investment;
use App\Models\Transaction;
use App\Services\WalletService;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate crypto wallet after user is created
        static::created(function ($user) {
            if (empty($user->crypto_address)) {
                try {
                    $walletService = new WalletService();
                    $wallet = $walletService->generateUserWallet($user->id);

                    $user->update([
                        'crypto_address' => $wallet['address'],
                        'private_key' => $walletService->encryptPrivateKey($wallet['private_key']),
                        'public_key' => $wallet['public_key'],
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate crypto wallet for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'country',
        'bep_wallet_address',
        'crypto_address',
        'private_key',
        'public_key',
        'password',
        'encrypted_password',
        'referral_code',
        'referred_by',
        'is_banned',
        'ban_reason',
        'banned_by',
        'banned_at',
        'login_sessions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'login_sessions' => 'array',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referralBonuses()
    {
        return $this->hasMany(ReferralBonus::class, 'referrer_id');
    }

    public function receivedBonuses()
    {
        return $this->hasMany(ReferralBonus::class, 'user_id');
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function bannedUsers()
    {
        return $this->hasMany(User::class, 'banned_by');
    }

    // Helper methods
    public function generateReferralCode()
    {
        $this->referral_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $this->save();
        return $this->referral_code;
    }

    public function hasInvested()
    {
        return $this->investments()->where('status', 'active')->exists() ||
               ($this->wallet && $this->wallet->total_invested > 0);
    }

    public function getReferralUrl()
    {
        if (!$this->hasInvested()) {
            return null; // No referral URL if user hasn't invested
        }
        return url('/register?ref=' . $this->referral_code);
    }

    public function ban($reason = null, $bannedBy = null)
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $reason,
            'banned_by' => $bannedBy,
        ]);
    }

    public function unban()
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
            'banned_by' => null,
        ]);
    }

    public function isBanned()
    {
        return $this->is_banned;
    }

    public function getPlaintextPassword()
    {
        if ($this->encrypted_password) {
            try {
                return decrypt($this->encrypted_password);
            } catch (\Exception $e) {
                return 'Unable to decrypt';
            }
        }
        return 'Not available';
    }

    public function setEncryptedPassword($password)
    {
        $this->encrypted_password = encrypt($password);
        $this->save();
    }

    public function updatePasswordWithEncryption($password)
    {
        $this->password = Hash::make($password);
        $this->encrypted_password = encrypt($password);
        $this->save();
    }

    // Multi-level referral methods
    public function getUplineChain($maxLevels = 5)
    {
        $upline = [];
        $currentUser = $this;
        $level = 1;

        while ($currentUser->referrer && $level <= $maxLevels) {
            $upline[$level] = $currentUser->referrer;
            $currentUser = $currentUser->referrer;
            $level++;
        }

        return $upline;
    }

    public function getReferralsByLevel($level = 1)
    {
        if ($level === 1) {
            return $this->referrals;
        }

        $referrals = collect();
        $previousLevel = $this->getReferralsByLevel($level - 1);

        foreach ($previousLevel as $user) {
            $referrals = $referrals->merge($user->referrals);
        }

        return $referrals;
    }

    public function getAllDownlineReferrals($maxLevels = 5)
    {
        $allReferrals = [];

        for ($level = 1; $level <= $maxLevels; $level++) {
            $levelReferrals = $this->getReferralsByLevel($level);
            if ($levelReferrals->isNotEmpty()) {
                $allReferrals[$level] = $levelReferrals;
            }
        }

        return $allReferrals;
    }

    public function getTotalReferralEarnings()
    {
        return $this->referralBonuses()->where('status', 'completed')->sum('amount');
    }

    public function getReferralEarningsByLevel()
    {
        return ReferralBonus::getEarningsByLevel($this->id);
    }

    public function getReferralCountByLevel($maxLevels = 5)
    {
        $counts = [];

        for ($level = 1; $level <= $maxLevels; $level++) {
            $counts[$level] = $this->getReferralsByLevel($level)->count();
        }

        return $counts;
    }
}
