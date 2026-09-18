<?php

namespace Database\Seeders;

use App\Models\FlightSchedule;
use App\Support\FlightGenerator;
use Illuminate\Database\Seeder;

/**
 * МИАТ-ын Улаанбаатар ↔ Франкфурт хуваарийн эхний тохиргоо.
 * 2026.09.18-ны нээлттэй эх сурвалжаар (flight.info, airportia, frankfurt-airport.com) OM137 нь
 * Да, Лх, Пү, Ба, Бя, Ня гарагт Франкфуртад 12:50-д буудаг, OM138 нь 14:20-д хөөрдөг.
 * Хуваарь улирлаар өөрчлөгддөг тул МИАТ-ын албан ёсны хуваариар админ хэсгээс шалгаж засна.
 */
class FlightScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $days = [1, 3, 4, 5, 6, 7];

        FlightSchedule::updateOrCreate(['code' => 'OM137', 'direction' => 'arrival'], [
            'origin' => 'UBN', 'destination' => 'FRA', 'weekdays' => $days,
            'local_time' => '12:50', 'valid_from' => '2026-05-20', 'valid_to' => null,
        ]);
        FlightSchedule::updateOrCreate(['code' => 'OM138', 'direction' => 'departure'], [
            'origin' => 'FRA', 'destination' => 'UBN', 'weekdays' => $days,
            'local_time' => '14:20', 'valid_from' => '2026-05-20', 'valid_to' => null,
        ]);

        app(FlightGenerator::class)->generate();
    }
}
