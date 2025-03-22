<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->dateTime('payment_date');
            $table->enum('status', ['paid', 'overdue']);
            $table->enum('payment_method', ['bank_transfer', 'crypto'])->default('bank_transfer');
            $table->string('crypto_currency')->nullable();
            $table->timestamps();
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->index(['loan_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayments');
    }
};
