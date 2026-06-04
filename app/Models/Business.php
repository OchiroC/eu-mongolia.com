<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    public const CATEGORIES = [
        'restaurant' => 'Ресторан / Хоол',
        'cafe' => 'Кафе / Баар',
        'grocery' => 'Дэлгүүр / Хүнс',
        'bakery' => 'Талх / Нарийн боов',
        'beauty' => 'Гоо сайхан / Үсчин',
        'retail' => 'Худалдаа',
        'service' => 'Үйлчилгээ',
        'other' => 'Бусад',
    ];

    protected $fillable = [
        'user_id', 'name', 'slug', 'category', 'description', 'city', 'country',
        'address', 'phone', 'email', 'website', 'facebook', 'hours', 'photo',
        'is_featured', 'featured_until', 'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isCurrentlyFeatured(): bool
    {
        return $this->is_featured && (! $this->featured_until || $this->featured_until->isFuture());
    }

    public function scopeOrderedForList(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN is_featured = 1 AND (featured_until IS NULL OR featured_until >= ?) THEN 0 ELSE 1 END', [now()])
            ->latest();
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
