<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professional extends Model
{
    protected $fillable = [
        'user_id', 'professional_category_id', 'name', 'slug', 'profession',
        'bio', 'photo', 'city', 'country', 'languages', 'services',
        'phone', 'email', 'website', 'facebook',
        'status', 'is_verified', 'is_featured', 'featured_until',
    ];

    protected $casts = [
        'languages' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProfessionalCategory::class, 'professional_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Онцлох (төлбөртэй) хугацаа дуусаагүй эсэх. */
    public function isCurrentlyFeatured(): bool
    {
        return $this->is_featured && (! $this->featured_until || $this->featured_until->isFuture());
    }

    /** Онцлохыг эхэнд, дараа нь шинэ дарааллаар. */
    public function scopeOrderedForList(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN is_featured = 1 AND (featured_until IS NULL OR featured_until >= ?) THEN 0 ELSE 1 END', [now()])
            ->orderByDesc('is_verified')
            ->latest();
    }
}
