<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            // Үнэ: тогтмол дүн, эсвэл price_type-аар тодорхойлно
            $table->decimal('price', 10, 2)->nullable();
            // fixed=тогтмол, negotiable=тохиролцоно (VB), free=үнэгүй, giveaway=дайна
            $table->enum('price_type', ['fixed', 'negotiable', 'free', 'giveaway'])->default('fixed');
            $table->enum('condition', ['new', 'used'])->nullable(); // шинэ/хуучин
            // Байршил
            $table->string('postal_code', 12)->nullable(); // PLZ
            $table->string('city', 120)->nullable();
            $table->string('country', 64)->nullable();
            // Холбоо барих
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('contact_email')->nullable();
            // Зураг (URL массив)
            $table->json('images')->nullable();
            // active=идэвхтэй, sold=зарагдсан, inactive=нуусан
            $table->enum('status', ['active', 'sold', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false); // онцолсон (төлбөртэй bump)
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['listing_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
