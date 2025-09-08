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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('title');         // Título corto
            $table->text('statement');       // Enunciado
            $table->smallInteger('year');
            $table->string('level', 20);
            $table->enum('phase', ['local', 'semifinal', 'final'])->default('local');
            $table->enum('session', ['matí', 'tarda'])->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
