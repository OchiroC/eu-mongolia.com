<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('image_path');           // баннер зураг (URL эсвэл upload зам)
            $table->string('link_url')->nullable();  // дарахад очих холбоос
            // Байршлын слот
            $table->enum('placement', ['home_top', 'home_sidebar', 'news_top', 'footer'])->default('home_top');
            // Төлөв: pending=хүлээгдэж буй, active=идэвхтэй, rejected=татгалзсан, expired=хугацаа дууссан
            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
            $table->decimal('price', 10, 2)->default(0);   // байршуулалтын төлбөр (EUR)
            $table->boolean('is_paid')->default(false);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['placement', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
