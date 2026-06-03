<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->nullable()->index(); // онцгой зочид тоолоход
            $table->string('ip', 45)->nullable();
            $table->string('path', 255)->nullable();
            $table->foreignId('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
