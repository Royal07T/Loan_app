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

            // ✅ Repayment Amount & Currency
            $table->decimal('repay_amount', 16, 8);
            $table->string('repay_currency')->default('NGN');
            $table->decimal('repay_amount_ngn', 16, 2)->nullable()->comment('Equivalent in NGN');

            // ✅ Payment Method
            $table->string('payment_method')->nullable();
            $table->dateTime('payment_date')->default(now());

            // ✅ Status Tracking
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');

            // ✅ Crypto Payments
            $table->string('crypto_txn_hash')->nullable()->comment('Transaction hash if paid in crypto');

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
