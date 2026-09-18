<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Хувийн бэлтгэлийн жагсаалт (хэрэглэгч гарын авлагын алхмыг хийснээ тэмдэглэнэ)
     * ба газрын зурагт зориулсан бизнес, мэргэжилтний координат.
     */
    public function up(): void
    {
        Schema::create('journey_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'guide_id']);
        });

        foreach (['businesses', 'professionals'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('lat', 9, 6)->nullable();
                $table->decimal('lng', 9, 6)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['businesses', 'professionals'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['lat', 'lng']);
            });
        }
        Schema::dropIfExists('journey_checks');
    }
};
