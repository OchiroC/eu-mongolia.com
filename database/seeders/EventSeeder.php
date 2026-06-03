<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'admin@eu-mongolia.test')->first();

        $samples = [
            ['Цагаан сар 2026 — Берлин', 'Берлин хотын монголчуудын Цагаан сарын баяр.', 'Berlin Saal', 'Берлин', 'Герман', 7],
            ['Монгол хоолны наадам — Прага', 'Үндэсний хоол, урлагийн тоглолт.', 'Praha Hall', 'Прага', 'Чех', 21],
        ];

        foreach ($samples as [$title, $desc, $venue, $city, $country, $inDays]) {
            $event = Event::firstOrCreate(
                ['slug' => Str::slug($title) ?: 'event-'.Str::random(5)],
                [
                    'organizer_id' => $organizer?->id,
                    'title' => $title,
                    'description' => $desc."\n\n".str_repeat('Дэлгэрэнгүй мэдээлэл удахгүй. ', 10),
                    'venue' => $venue,
                    'city' => $city,
                    'country' => $country,
                    'starts_at' => now()->addDays($inDays)->setTime(18, 0),
                    'ends_at' => now()->addDays($inDays)->setTime(23, 0),
                    'status' => 'published',
                ]
            );

            if ($event->ticketTypes()->count() === 0) {
                $event->ticketTypes()->createMany([
                    ['name' => 'Энгийн', 'price' => 15, 'quantity' => 200],
                    ['name' => 'VIP', 'price' => 40, 'quantity' => 50],
                ]);
            }
        }
    }
}
