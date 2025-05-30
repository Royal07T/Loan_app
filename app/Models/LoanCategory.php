<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'interest_rate',
        'max_term_months',
        'late_payment_fee',
        'processing_fee',
        'requires_collateral',
        'required_documents',
        'is_active'
    ];

    protected $casts = [
        'required_documents' => 'array',
        'requires_collateral' => 'boolean',
        'is_active' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'late_payment_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
    ];

    /**
     * Get all loans in this category
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Calculate monthly interest rate
     */
    public function getMonthlyInterestRateAttribute(): float
    {
        return $this->interest_rate / 12;
    }

    /**
     * Calculate the maximum monthly payment for a given loan amount
     */
    public function calculateMaxMonthlyPayment(float $loanAmount): float
    {
        $monthlyRate = $this->monthly_interest_rate / 100;
        $months = $this->max_term_months;
        
        // PMT formula: PMT = P * (r(1+r)^n)/((1+r)^n-1)
        $monthlyPayment = $loanAmount * 
            ($monthlyRate * pow(1 + $monthlyRate, $months)) / 
            (pow(1 + $monthlyRate, $months) - 1);
            
        return round($monthlyPayment, 2);
    }

    /**
     * Check if a loan amount is within the allowed range
     */
    public function isAmountAllowed(float $amount): bool
    {
        return $amount >= $this->min_amount && $amount <= $this->max_amount;
    }

    /**
     * Calculate processing fee for a given loan amount
     */
    public function calculateProcessingFee(float $loanAmount): float
    {
        return round($loanAmount * ($this->processing_fee / 100), 2);
    }
}
