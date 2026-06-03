<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id', 'title', 'image_path', 'link_url', 'placement',
        'status', 'price', 'is_paid', 'starts_at', 'ends_at', 'sort_order',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function scopeLive(Builder $query, string $placement): Builder
    {
        $today = now()->toDateString();

        return $query->where('placement', $placement)
            ->where('status', 'active')
            ->where('is_paid', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today))
            ->orderBy('sort_order');
    }
}
