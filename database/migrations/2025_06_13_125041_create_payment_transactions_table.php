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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('gateway'); // paystack, stripe, etc.
            $table->string('reference')->unique(); // Our internal reference
            $table->string('gateway_reference')->nullable(); // Gateway's reference
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('NGN');
            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'completed',
                'succeeded',
                'failed',
                'cancelled',
                'expired'
            ])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional data
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['loan_id', 'status']);
            $table->index(['reference']);
            $table->index(['gateway', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
