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
        // Schema::create('download_requests', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('file_id')->constrained('documents');
        //     $table->foreignId('user_id')->constrained('users');
        //     $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_requests');
    }
};
