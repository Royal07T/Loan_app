<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index(); // Indexed FK
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(10.00);
            $table->integer('duration')->comment('Loan duration in months');
            $table->string('status')->default('pending'); // Changed ENUM to String for flexibility
            $table->date('due_date')->nullable();
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->enum('currency', ['NGN', 'BTC', 'ETH', 'USDT'])->default('NGN');
            $table->timestamps();
            $table->softDeletes(); // Allow soft deletes
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'due_date']); // Optimized for overdue checks
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
// Compare this snippet from app/Models/Loan.php:
