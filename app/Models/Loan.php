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
        'loan_category_id',
        'amount',
        'interest_rate',
        'duration',
        'status',
        'due_date',
        'late_fee',
        'currency',
        'processing_fee',
        'collateral_info',
        'documents'
    ];

    protected $casts = [
        'due_date' => 'date',
        'collateral_info' => 'array',
        'documents' => 'array',
        'processing_fee' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($loan) {
            if (!$loan->due_date) {
                $loan->due_date = Carbon::now()->addMonths($loan->duration);
            }
            
            // Set processing fee from loan category if not set
            if (!$loan->processing_fee && $loan->category) {
                $loan->processing_fee = $loan->category->calculateProcessingFee($loan->amount);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
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
        if ($this->isOverdue() && $this->category) {
            $this->increment('late_fee', $this->category->late_payment_fee);
        }
    }

    public function updateLoanStatus()
    {
        $totalPaid = $this->repayments()->sum('amount_paid');
        $totalDue = $this->getTotalDueAmount();
        
        if ($totalPaid >= $totalDue) {
            $this->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $this->update(['status' => 'partial']);
        }
    }

    public function getTotalDueAmount(): float
    {
        return $this->amount + $this->late_fee + $this->processing_fee;
    }

    public function getRemainingAmount(): float
    {
        return $this->getTotalDueAmount() - $this->repayments()->sum('amount_paid');
    }

    public function getNextPaymentDate(): ?Carbon
    {
        $lastPayment = $this->repayments()->latest()->first();
        if (!$lastPayment) {
            return Carbon::now();
        }
        return $lastPayment->created_at->addMonth();
    }

    public function calculateMonthlyPayment(): float
    {
        $monthlyRate = ($this->interest_rate / 12) / 100;
        $months = $this->duration;
        
        return round($this->amount * 
            ($monthlyRate * pow(1 + $monthlyRate, $months)) / 
            (pow(1 + $monthlyRate, $months) - 1), 2);
    }

    public function getPaymentSchedule(): array
    {
        $schedule = [];
        $monthlyPayment = $this->calculateMonthlyPayment();
        $balance = $this->amount;
        $startDate = $this->created_at ?? Carbon::now();

        for ($month = 1; $month <= $this->duration; $month++) {
            $interestPayment = $balance * (($this->interest_rate / 12) / 100);
            $principalPayment = $monthlyPayment - $interestPayment;
            $balance -= $principalPayment;

            $schedule[] = [
                'month' => $month,
                'date' => $startDate->copy()->addMonths($month),
                'payment' => $monthlyPayment,
                'principal' => $principalPayment,
                'interest' => $interestPayment,
                'balance' => max(0, $balance)
            ];
        }

        return $schedule;
    }
}
