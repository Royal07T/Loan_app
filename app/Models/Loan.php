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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && Carbon::now()->greaterThan($this->due_date);
    }

    public function applyLateFee()
    {
        if ($this->isOverdue()) {
            $this->update(['late_fee' => $this->amount * 0.02]);
        }
    }
}
