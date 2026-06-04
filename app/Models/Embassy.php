<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Embassy extends Model
{
    protected $table = 'embassies';

    public const KINDS = [
        'embassy' => 'Элчин сайдын яам',
        'consulate' => 'Консулын газар',
        'honorary' => 'Хүндэт консул',
    ];

    protected $fillable = [
        'name', 'kind', 'country', 'city', 'address', 'phone', 'emergency_phone',
        'email', 'website', 'hours', 'notes', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getKindLabelAttribute(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }
}
