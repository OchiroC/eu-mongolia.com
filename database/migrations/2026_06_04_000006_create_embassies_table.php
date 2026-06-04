<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embassies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kind', 20)->default('embassy'); // embassy, consulate, honorary
            $table->string('country', 64);
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('hours')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embassies');
    }
};
