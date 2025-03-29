<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'crypto_currency',
        'late_fee'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($repayment) {
            // Check if late fee applies
            if ($repayment->loan->isOverdue()) {
                $repayment->late_fee = $repayment->loan->late_fee;
            }

            // Ensure crypto currency is set for crypto payments
            if ($repayment->payment_method === 'crypto' && is_null($repayment->crypto_currency)) {
                throw new \Exception("Crypto currency type is required for crypto payments");
            }
        });

        static::created(function ($repayment) {
            // Update loan status on payment
            $repayment->loan->updateLoanStatus();
        });
    }
}
