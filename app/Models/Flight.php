<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Flight extends Model
{
    /** Нислэгийн цагийг Франкфуртын цагаар харуулна. */
    public const TZ = 'Europe/Berlin';

    public const STATUSES = ['scheduled' => 'Хуваарийн дагуу', 'delayed' => 'Хойшилсон', 'cancelled' => 'Цуцлагдсан'];

    protected $fillable = ['flight_schedule_id', 'code', 'direction', 'origin', 'destination', 'scheduled_at', 'status', 'note', 'slug'];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(FlightSchedule::class, 'flight_schedule_id');
    }

    public function passengers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'flight_passengers')->withPivot('destination')->withTimestamps();
    }

    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class);
    }

    /** Одоогоос хойш (буусан нислэгийг 6 цагийн турш үлдээнэ, угтах хүмүүст хэрэгтэй). */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>=', now()->subHours(6));
    }

    public function localTime(): Carbon
    {
        return $this->scheduled_at->copy()->setTimezone(self::TZ);
    }

    /**
     * Самбар, жагсаалтад харуулах товч мэдээлэл.
     *
     * @return array<string, mixed>
     */
    public function card(): array
    {
        $local = $this->localTime();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'code' => $this->code,
            'direction' => $this->direction,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'date' => $local->format('Y-m-d'),
            'time' => $local->format('H:i'),
            'weekday' => FlightSchedule::WEEKDAYS[$local->isoWeekday()],
            'status' => $this->status,
            'status_label' => self::STATUSES[$this->status] ?? $this->status,
            'note' => $this->note,
            'passengers_count' => $this->passengers_count ?? null,
            'rides_count' => $this->rides_count ?? null,
            'parcels_count' => $this->parcels_count ?? null,
        ];
    }

    public static function slugFor(string $code, Carbon $local): string
    {
        return strtolower($code).'-'.$local->format('Y-m-d');
    }
}
