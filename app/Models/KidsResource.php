<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KidsResource extends Model
{
    public const CATEGORIES = [
        'language' => 'Хэл сурах',
        'books' => 'Ном / Үлгэр',
        'video' => 'Дуу / Видео',
        'school' => 'Сургууль / Бүлгэм',
        'culture' => 'Соёл / Уламжлал',
        'games' => 'Тоглоом / Апп',
        'other' => 'Бусад',
    ];

    protected $fillable = [
        'title', 'category', 'description', 'url', 'city', 'country',
        'contact', 'age_range', 'is_featured', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
