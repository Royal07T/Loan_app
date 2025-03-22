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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 8); // Supports large amounts for crypto
            $table->decimal('interest_rate', 5, 2)->default(10.00);
            $table->integer('duration')->comment('Loan duration in months');
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->date('due_date')->nullable()->comment('Loan due date');
            $table->decimal('late_fee', 20, 8)->default(0)->comment('Late fee for overdue loans');

            // Crypto Support
            $table->enum('loan_type', ['fiat', 'crypto'])->default('fiat');
            $table->string('crypto_currency')->nullable()->comment('BTC, ETH, USDT, etc.');
            $table->decimal('exchange_rate', 20, 8)->nullable()->comment('Exchange rate at loan approval');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
