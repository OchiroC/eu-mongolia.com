<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_city');
            $table->string('from_country', 64)->nullable();
            $table->string('to_city');
            $table->string('to_country', 64)->nullable();
            $table->timestamp('depart_at');
            $table->unsignedTinyInteger('seats')->default(1);
            $table->string('price')->nullable();        // "€20", "Тохиролцоно"
            $table->text('notes')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'depart_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
