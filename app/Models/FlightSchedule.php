<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlightSchedule extends Model
{
    /** ISO 7 хоногийн өдрүүд (1 = Даваа). */
    public const WEEKDAYS = [1 => 'Да', 2 => 'Мя', 3 => 'Лх', 4 => 'Пү', 5 => 'Ба', 6 => 'Бя', 7 => 'Ня'];

    protected $fillable = ['code', 'direction', 'origin', 'destination', 'weekdays', 'local_time', 'valid_from', 'valid_to'];

    protected $casts = [
        'weekdays' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }
}
