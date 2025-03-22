<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'user_id',
        'amount_paid',
        'payment_date',
        'status',
        'payment_method',
        'repayment_currency', // NEW: 'fiat' or 'crypto'
        'crypto_currency', // NEW: BTC, ETH, USDT, etc.
        'exchange_rate', // NEW: Exchange rate at repayment
    ];

    // A repayment belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A repayment belongs to a loan
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
