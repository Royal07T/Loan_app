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

    // A user can apply for multiple loans
    public function loans()
    {
        return $this->hasMany(Loan::class, 'user_id', 'id');
    }


    // A user can make multiple repayments
    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class, 'user_id', 'id');
    }
}
