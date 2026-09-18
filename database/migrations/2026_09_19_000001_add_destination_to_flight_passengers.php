<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Франкфуртаас цааш хаашаа явах (App\Support\Transit-ийн slug). Хоосон бол сонгоогүй. */
    public function up(): void
    {
        Schema::table('flight_passengers', function (Blueprint $table) {
            $table->string('destination', 30)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('flight_passengers', function (Blueprint $table) {
            $table->dropColumn('destination');
        });
    }
};
