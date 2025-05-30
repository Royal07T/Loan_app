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
        Schema::create('loan_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Annual interest rate
            $table->integer('max_term_months');
            $table->decimal('late_payment_fee', 10, 2)->default(0);
            $table->decimal('processing_fee', 5, 2)->default(0); // Percentage
            $table->boolean('requires_collateral')->default(false);
            $table->json('required_documents')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_categories');
    }
};
