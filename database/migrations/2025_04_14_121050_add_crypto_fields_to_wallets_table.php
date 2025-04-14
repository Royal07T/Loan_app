<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('wallet_address')->nullable()->after('name');
            $table->decimal('crypto_balance', 20, 8)->default(0)->after('balance');
            $table->decimal('fiat_balance', 15, 2)->default(0)->after('crypto_balance');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'wallet_address', 'crypto_balance', 'fiat_balance']);
        });
    }
};
