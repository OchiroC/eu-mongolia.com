<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('professional_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('profession')->nullable();   // "Гэр бүлийн хуульч" гэх мэт гарчиг
            $table->longText('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 64)->nullable();
            $table->json('languages')->nullable();
            $table->text('services')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('contact_reveals')->default(0);
            $table->timestamps();
            $table->index(['status', 'professional_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
