<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 20)->default('room');   // room, wg, apartment, seeking
            $table->string('city');
            $table->string('country', 64)->nullable();
            $table->string('district')->nullable();
            $table->unsignedInteger('price')->nullable();   // €/сар
            $table->unsignedInteger('deposit')->nullable();
            $table->string('rooms', 10)->nullable();        // "1", "1.5", "2"
            $table->unsignedInteger('size')->nullable();    // m²
            $table->date('available_from')->nullable();
            $table->boolean('furnished')->default(false);
            $table->string('gender_pref', 10)->default('any'); // any, male, female
            $table->longText('description')->nullable();
            $table->json('images')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'type', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_posts');
    }
};
