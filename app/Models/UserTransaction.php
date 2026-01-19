<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTransaction extends Model
{
    use HasFactory;

    protected $table = 'user_transactions';

    protected $fillable = [
        'user_id',
        'from_address',
        'to_address',
        'amount',
        'token',
        'status',
        'tx_hash',
        'invests_id',
        'is_read',
        'block_number',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
