<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('category', 30)->index();
            $table->string('country', 64)->nullable();
            $table->unsignedBigInteger('best_answer_id')->nullable();
            $table->unsignedInteger('answers_count')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
