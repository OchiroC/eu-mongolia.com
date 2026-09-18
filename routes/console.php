<?php

use Illuminate\Foundation\Inspiring;
use App\Support\FlightGenerator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Нислэгийн хуваариас ирэх долоо хоногуудын нислэгийг үүсгэнэ (өдөр бүр).
Artisan::command('flights:generate {--weeks=10}', function (FlightGenerator $generator) {
    $count = $generator->generate((int) $this->option('weeks'));
    $this->info("Шинээр {$count} нислэг үүслээ.");
})->purpose('Нислэгийн хуваариас нислэг үүсгэх');

Schedule::command('flights:generate')->dailyAt('03:00');
