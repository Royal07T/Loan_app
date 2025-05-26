<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Transaction;

/**
 * App\Models\Wallet
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $wallet_address
 * @property float $crypto_balance
 * @property float $fiat_balance
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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

    /**
     * Get the user that owns this wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions related to this wallet.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
