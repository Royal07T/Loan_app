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
        'due_date',
        'late_fee',
        'currency'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($loan) {
            if (!$loan->due_date) {
                $loan->due_date = Carbon::now()->addMonths($loan->duration);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repayments()
    {
        return $this->hasMany(\App\Models\Repayment::class, 'loan_id');
    }
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && Carbon::now()->greaterThan($this->due_date);
    }

    public function applyLateFee()
    {
        if ($this->isOverdue()) {
            $this->increment('late_fee', $this->amount * 0.02);
        }
    }

    public function updateLoanStatus()
    {
        $totalPaid = $this->repayments()->sum('amount_paid');
        if ($totalPaid >= ($this->amount + $this->late_fee)) {
            $this->update(['status' => 'paid']);
        }
    }
}
//             $this->update(['late_fee' => $newLateFee]);
//             $this->update(['status' => 'paid']);`
