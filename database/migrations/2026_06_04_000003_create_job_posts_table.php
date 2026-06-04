<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('company')->nullable();
            $table->longText('description');
            $table->string('employment_type', 20)->default('full_time'); // full_time, part_time, temporary, internship, gig
            $table->string('category', 30)->index();
            $table->string('city')->nullable();
            $table->string('country', 64)->nullable();
            $table->string('salary')->nullable();        // уян хатан: "€12/цаг", "Тохиролцоно"
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('apply_url')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'category', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
