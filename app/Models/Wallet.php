<?php

// app/Models/Wallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'wallet_address', 'crypto_balance', 'fiat_balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
