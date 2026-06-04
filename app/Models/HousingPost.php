<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousingPost extends Model
{
    public const TYPES = [
        'room' => 'Өрөө түрээслүүлнэ',
        'wg' => 'Хамтран түрээслэх (WG)',
        'apartment' => 'Орон сууц түрээслүүлнэ',
        'seeking' => 'Байр / өрөө хайж байна',
    ];

    public const GENDERS = [
        'any' => 'Хамаагүй',
        'male' => 'Эрэгтэй',
        'female' => 'Эмэгтэй',
    ];

    protected $fillable = [
        'user_id', 'title', 'slug', 'type', 'city', 'country', 'district',
        'price', 'deposit', 'rooms', 'size', 'available_from', 'furnished',
        'gender_pref', 'description', 'images', 'contact_phone', 'status',
    ];

    protected $casts = [
        'images' => 'array',
        'furnished' => 'boolean',
        'available_from' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getCoverAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }
}
