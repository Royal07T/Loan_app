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
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 20, 8);
            $table->date('payment_date');
            $table->enum('status', ['paid', 'overdue'])->default('paid');
            $table->string('payment_method');

            // Crypto Support
            $table->enum('repayment_currency', ['fiat', 'crypto'])->default('fiat');
            $table->string('crypto_currency')->nullable()->comment('BTC, ETH, USDT, etc.');
            $table->decimal('exchange_rate', 20, 8)->nullable()->comment('Exchange rate at repayment');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};
