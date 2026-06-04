<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kids_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 30)->index();
            $table->text('description')->nullable();
            $table->string('url')->nullable();        // гадаад линк (видео, апп, ном)
            $table->string('city')->nullable();       // сургууль/бүлгэм бол
            $table->string('country', 64)->nullable();
            $table->string('contact')->nullable();
            $table->string('age_range', 40)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kids_resources');
    }
};
