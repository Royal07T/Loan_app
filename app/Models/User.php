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
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
}
//             $this->update(['late_fee' => $newLateFee]);
