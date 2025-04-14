<?php

// app/Models/Wallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['name', 'balance'];
}
