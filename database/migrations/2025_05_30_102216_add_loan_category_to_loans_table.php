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
            $table->foreignId('loan_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->json('collateral_info')->nullable();
            $table->json('documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['loan_category_id']);
            $table->dropColumn('loan_category_id');
            $table->dropColumn('processing_fee');
            $table->dropColumn('collateral_info');
            $table->dropColumn('documents');
        });
    }
};
