<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Add new wallet management fields
            $table->enum('wallet_type', ['metamask', 'hardware', 'external'])->default('external')->after('wallet_address');
            $table->boolean('is_active')->default(true)->after('fiat_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['wallet_type', 'is_active']);
        });
    }
};
