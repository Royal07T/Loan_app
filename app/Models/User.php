<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    // A user can apply for multiple loans
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // A user can make multiple repayments
    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }
}
