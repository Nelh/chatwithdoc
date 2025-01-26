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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->longText('context')->nullable();
            $table->string('template')->nullable();
            $table->string('type')->nullable()->default('created');
            $table->string('file_path')->nullable();
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('status')->nullable()->default('pending');
            $table->json('code')->nullable();
            $table->json('signatures')->nullable();
            $table->json('meta')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->boolean('expiration_date_reminder')->default(false);
            $table->unsignedInteger('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
