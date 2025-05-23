<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'wallet_address',
        'crypto_balance',
        'fiat_balance',
        'balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
