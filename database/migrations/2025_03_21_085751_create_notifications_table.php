<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // Supports multiple notification targets
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps(0); // Ensures timestamps have default values
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
