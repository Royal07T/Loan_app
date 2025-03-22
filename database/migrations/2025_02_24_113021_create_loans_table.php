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

            // ✅ Loan Amount & Currency
            $table->decimal('borrow_amount', 16, 8); // Supports crypto (up to 8 decimal places)
            $table->string('borrow_currency')->default('NGN'); // NGN, BTC, ETH, USDT
            $table->decimal('borrow_amount_ngn', 16, 2)->nullable()->comment('Equivalent amount in NGN');

            // ✅ Loan Terms
            $table->decimal('interest_rate', 5, 2)->default(10.00);
            $table->integer('duration')->comment('Loan duration in months');
            $table->date('due_date')->nullable()->comment('Loan due date');

            // ✅ Loan Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');

            // ✅ Late Fee & Tracking
            $table->decimal('late_fee', 10, 2)->default(0)->comment('Late fee for overdue loans');

            // ✅ Crypto Transactions
            $table->string('crypto_txn_hash')->nullable()->comment('Transaction hash if paid in crypto');

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
