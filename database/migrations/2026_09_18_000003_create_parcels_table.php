<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ачаа, илгээмжийн зар: "ачаанд сул зай байна" (offer) эсвэл "авч явах хүн хэрэгтэй" (request).
     */
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flight_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['offer', 'request']);
            $table->enum('direction', ['to_germany', 'to_mongolia']);
            $table->date('travel_date')->nullable();     // нислэг сонгоогүй үед
            $table->string('from_city', 120);
            $table->string('to_city', 120);
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->string('price', 60)->nullable();      // "5 €/кг", "Тохиролцоно"
            $table->text('description');
            $table->string('contact_phone', 40)->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'type', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
