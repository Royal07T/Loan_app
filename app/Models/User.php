<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kyc_status',
        'kyc_reference',
        'kyc_verified_at',
        'kyc_attempts',
        'kyc_data',
        'kyc_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'kyc_data' => 'array',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    public function getTotalOutstandingLoans(): float
    {
        return $this->loans()->where('status', '!=', 'paid')->sum('amount');
    }

    public function hasOverdueLoans(): bool
    {
        return $this->loans()->where('status', '!=', 'paid')->where('due_date', '<', now())->exists();
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Check if user has completed KYC verification
     */
    public function isKYCVerified(): bool
    {
        return $this->kyc_status === 'verified' && $this->kyc_verified_at !== null;
    }

    /**
     * Check if user has pending KYC verification
     */
    public function hasPendingKYC(): bool
    {
        return $this->kyc_status === 'pending';
    }

    /**
     * Check if user can retry KYC verification
     */
    public function canRetryKYC(): bool
    {
        $maxAttempts = config('kyc.settings.max_attempts', 3);
        return ($this->kyc_attempts ?? 0) < $maxAttempts;
    }

    /**
     * Get KYC verification age in days
     */
    public function getKYCAgeInDays(): ?int
    {
        if (!$this->kyc_verified_at) {
            return null;
        }

        return $this->kyc_verified_at->diffInDays(now());
    }

    /**
     * Check if KYC verification is expired
     */
    public function isKYCExpired(): bool
    {
        if (!$this->kyc_verified_at) {
            return false;
        }

        $maxAge = config('kyc.settings.kyc_expiry_days', 365);
        return $this->getKYCAgeInDays() > $maxAge;
    }

    /**
     * Get KYC status with expiry information
     */
    public function getKYCStatusWithExpiry(): array
    {
        $status = $this->kyc_status ?? 'not_started';
        $ageInDays = $this->getKYCAgeInDays();
        $isExpired = $this->isKYCExpired();

        return [
            'status' => $status,
            'verified_at' => $this->kyc_verified_at,
            'age_in_days' => $ageInDays,
            'is_expired' => $isExpired,
            'can_retry' => $this->canRetryKYC(),
            'attempts' => $this->kyc_attempts ?? 0,
            'max_attempts' => config('kyc.settings.max_attempts', 3),
        ];
    }
}


//             $this->update(['late_fee' => $newLateFee]);
