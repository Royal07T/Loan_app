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
        Schema::table('loans', function (Blueprint $table) {
            // Only add fields that don't already exist
            if (!Schema::hasColumn('loans', 'loan_type')) {
                $table->enum('loan_type', ['fiat', 'crypto'])->default('fiat')->after('currency');
            }
            if (!Schema::hasColumn('loans', 'crypto_currency')) {
                $table->string('crypto_currency')->nullable()->after('loan_type');
            }
            if (!Schema::hasColumn('loans', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 2)->nullable()->after('crypto_currency');
            }
            if (!Schema::hasColumn('loans', 'purpose')) {
                $table->text('purpose')->nullable()->after('exchange_rate');
            }
            if (!Schema::hasColumn('loans', 'processing_fee')) {
                $table->decimal('processing_fee', 12, 2)->default(0)->after('purpose');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'loan_type',
                'crypto_currency',
                'exchange_rate',
                'purpose',
                'processing_fee'
            ]);
        });
    }
};
