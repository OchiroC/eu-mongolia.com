<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guide extends Model
{
    /** Тогтмол сэдвүүд (key => Монгол нэр). */
    public const TOPICS = [
        'visa' => 'Виз / ВНЖ',
        'registration' => 'Бүртгэл (Anmeldung)',
        'insurance' => 'Даатгал',
        'tax' => 'Татвар / Санхүү',
        'work' => 'Ажил эрхлэлт',
        'study' => 'Боловсрол / Оюутан',
        'health' => 'Эрүүл мэнд',
        'driving' => 'Жолооны үнэмлэх',
        'bank' => 'Банк / Карт',
        'travel' => 'Нислэг, ачаа',
        'housing' => 'Орон сууц',
        'other' => 'Бусад',
    ];

    /** Аяллын үе шат (нүүрний "Аяллын зам" хэсэг). Дараалал нь харагдах дараалал. */
    public const STAGES = [
        'before' => 'Нисэхээс өмнө',
        'arrival' => 'Буух өдөр',
        'first_weeks' => 'Эхний 14 хоног',
    ];

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body', 'cover_image',
        'topic', 'country', 'stage', 'stage_order', 'is_featured', 'status', 'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'stage_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function getTopicLabelAttribute(): string
    {
        return self::TOPICS[$this->topic] ?? $this->topic;
    }
}
