<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run()
    {
        Wallet::create([
            'name' => 'Central Wallet',
            'balance' => 1000000.00
        ]);
    }
}
