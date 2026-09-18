<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
    public const TYPES = [
        'offer' => 'Ачаанд сул зай байна',
        'request' => 'Авч явах хүн хэрэгтэй',
    ];

    public const DIRECTIONS = [
        'to_germany' => 'Монгол → Герман',
        'to_mongolia' => 'Герман → Монгол',
    ];

    protected $fillable = [
        'user_id', 'flight_id', 'type', 'direction', 'travel_date', 'from_city', 'to_city',
        'weight_kg', 'price', 'description', 'contact_phone', 'status',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'weight_kg' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
