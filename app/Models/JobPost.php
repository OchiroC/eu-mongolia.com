<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    public const TYPES = [
        'full_time' => 'Бүтэн цаг',
        'part_time' => 'Цагийн',
        'temporary' => 'Түр',
        'internship' => 'Дадлага',
        'gig' => 'Нэг удаагийн',
    ];

    public const CATEGORIES = [
        'service' => 'Үйлчилгээ / Ресторан',
        'construction' => 'Бүтээн байгуулалт',
        'cleaning' => 'Цэвэрлэгээ',
        'care' => 'Асаргаа / Эрүүл мэнд',
        'logistics' => 'Тээвэр / Агуулах',
        'office' => 'Оффис / Захиргаа',
        'it' => 'IT / Технологи',
        'retail' => 'Худалдаа',
        'other' => 'Бусад',
    ];

    protected $fillable = [
        'user_id', 'title', 'slug', 'company', 'description', 'employment_type',
        'category', 'city', 'country', 'salary',
        'contact_email', 'contact_phone', 'apply_url', 'status',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->employment_type] ?? $this->employment_type;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
