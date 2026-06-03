<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'listing_category_id', 'title', 'slug', 'description',
        'price', 'price_type', 'condition', 'postal_code', 'city', 'country',
        'contact_name', 'contact_phone', 'contact_email', 'images',
        'status', 'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'is_featured' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'listing_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Энэ зарыг хадгалсан хэрэглэгчид.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Үндсэн зураг (эхний зураг) эсвэл null.
     */
    public function getCoverAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }
}
