<?php

namespace App\Support;

use App\Models\Flight;
use App\Models\FlightSchedule;
use Illuminate\Support\Carbon;

/**
 * Хуваарийн дүрмээс ирэх долоо хоногуудын нислэгийг үүсгэнэ.
 * Давтан ажиллуулахад аюулгүй: slug-аар нь олж, байгааг дахин үүсгэхгүй.
 * Цуцлагдсан эсвэл гараар засагдсан (хойшилсон) нислэгийн цагийг хөндөхгүй.
 */
class FlightGenerator
{
    public function generate(int $weeks = 10): int
    {
        $created = 0;
        foreach (FlightSchedule::all() as $schedule) {
            $created += $this->forSchedule($schedule, $weeks);
        }

        return $created;
    }

    public function forSchedule(FlightSchedule $schedule, int $weeks = 10): int
    {
        $today = Carbon::now(Flight::TZ)->startOfDay();
        $start = $schedule->valid_from->copy()->shiftTimezone(Flight::TZ)->max($today);
        $end = $today->copy()->addWeeks($weeks);
        if ($schedule->valid_to) {
            $end = $end->min($schedule->valid_to->copy()->shiftTimezone(Flight::TZ));
        }

        [$h, $m] = array_map('intval', explode(':', (string) $schedule->local_time));
        $created = 0;

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if (! in_array($day->isoWeekday(), array_map('intval', $schedule->weekdays), true)) {
                continue;
            }

            $local = $day->copy()->setTime($h, $m);
            $slug = Flight::slugFor($schedule->code, $local);
            $flight = Flight::firstWhere('slug', $slug);

            if (! $flight) {
                Flight::create([
                    'flight_schedule_id' => $schedule->id,
                    'code' => $schedule->code,
                    'direction' => $schedule->direction,
                    'origin' => $schedule->origin,
                    'destination' => $schedule->destination,
                    'scheduled_at' => $local->copy()->utc(),
                    'status' => 'scheduled',
                    'slug' => $slug,
                ]);
                $created++;
            } elseif ($flight->status === 'scheduled' && $flight->flight_schedule_id === $schedule->id) {
                // Хуваарийн цаг өөрчлөгдсөн бол ирээдүйн нислэгийг дагуулж шинэчилнэ.
                $flight->update(['scheduled_at' => $local->copy()->utc()]);
            }
        }

        return $created;
    }
}
