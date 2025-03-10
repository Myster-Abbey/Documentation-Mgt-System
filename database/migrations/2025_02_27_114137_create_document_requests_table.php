<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     /*   Schema::create('download_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('documents');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        }); */
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'cancelled', 'archived'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
