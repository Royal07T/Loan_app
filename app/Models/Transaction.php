<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'counterparty',
        'amount',
        'hash',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
// This model represents a transaction in the wallet system.
