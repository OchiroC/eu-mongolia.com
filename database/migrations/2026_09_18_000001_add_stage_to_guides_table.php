<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Гарын авлагыг аяллын үе шатаар (нисэхээс өмнө, буух өдөр, эхний 14 хоног) ангилна.
     * stage = null бол нүүрний "Аяллын зам" хэсэгт харагдахгүй.
     */
    public function up(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->string('stage', 20)->nullable()->after('country')->index();
            $table->unsignedSmallInteger('stage_order')->default(0)->after('stage');
        });
    }

    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn(['stage', 'stage_order']);
        });
    }
};
