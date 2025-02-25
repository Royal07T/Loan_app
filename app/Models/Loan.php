<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'interest_rate',
        'duration',
        'status',
        'due_date', // Added due_date
        'late_fee', // Added late_fee
    ];

    // A loan belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A loan has multiple repayments
    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    /**
     * ✅ Check if the loan is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && Carbon::now()->greaterThan(Carbon::parse($this->due_date));
    }

    /**
     * ✅ Apply late fee if the loan is overdue.
     */
    public function applyLateFee()
    {
        if ($this->isOverdue()) {
            $lateFee = $this->amount * 0.02; // 2% Late Fee
            $this->update(['late_fee' => $lateFee]);
        }
    }
}
