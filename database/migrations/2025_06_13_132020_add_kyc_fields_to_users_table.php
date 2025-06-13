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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('kyc_status', ['not_started', 'pending', 'verified', 'rejected', 'expired', 'cancelled'])
                  ->default('not_started')
                  ->after('email_verified_at');
            
            $table->string('kyc_reference')->nullable()->after('kyc_status');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_reference');
            $table->integer('kyc_attempts')->default(0)->after('kyc_verified_at');
            $table->json('kyc_data')->nullable()->after('kyc_attempts');
            $table->string('kyc_provider')->nullable()->after('kyc_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_reference',
                'kyc_verified_at',
                'kyc_attempts',
                'kyc_data',
                'kyc_provider',
            ]);
        });
    }
};
