<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Нислэгийн хуваарь (7 хоногийн давтамжийн дүрэм) ба түүнээс үүсэх нислэгүүд.
     * Хуваарь улирлаар өөрчлөгддөг тул админ удирдана; нислэг бүрийг тусад нь цуцалж, цагийг нь засаж болно.
     */
    public function up(): void
    {
        Schema::create('flight_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);                 // OM137
            $table->enum('direction', ['arrival', 'departure']); // Франкфуртад ирэх / Франкфуртаас хөдлөх
            $table->string('origin', 3);                 // UBN
            $table->string('destination', 3);            // FRA
            $table->json('weekdays');                    // ISO: 1 = Даваа ... 7 = Ням
            $table->time('local_time');                  // Франкфуртын цагаар буух/хөөрөх цаг
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->timestamps();
        });

        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 10);
            $table->enum('direction', ['arrival', 'departure']);
            $table->string('origin', 3);
            $table->string('destination', 3);
            $table->timestamp('scheduled_at');           // UTC
            $table->enum('status', ['scheduled', 'delayed', 'cancelled'])->default('scheduled');
            $table->string('note')->nullable();
            $table->string('slug')->unique();            // om137-2026-10-14
            $table->timestamps();
            $table->index(['scheduled_at', 'direction']);
        });

        Schema::create('flight_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['flight_id', 'user_id']);
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->foreignId('flight_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flight_id');
        });
        Schema::dropIfExists('flight_passengers');
        Schema::dropIfExists('flights');
        Schema::dropIfExists('flight_schedules');
    }
};
