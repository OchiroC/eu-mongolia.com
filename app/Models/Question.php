<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const TOPICS = [
        'visa' => 'Виз / Цагаачлал',
        'work' => 'Ажил',
        'housing' => 'Орон сууц',
        'study' => 'Боловсрол',
        'health' => 'Эрүүл мэнд',
        'legal' => 'Хууль эрх зүй',
        'daily' => 'Өдөр тутам',
        'other' => 'Бусад',
    ];

    protected $fillable = [
        'user_id', 'title', 'slug', 'body', 'category', 'country',
        'best_answer_id', 'answers_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::TOPICS[$this->category] ?? $this->category;
    }
}
