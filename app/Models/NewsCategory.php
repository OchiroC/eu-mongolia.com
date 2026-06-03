<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsCategory extends Model
{
    use HasFactory;

    public const UNCATEGORIZED_SLUG = 'uncategorized';

    protected $fillable = ['parent_id', 'name', 'slug', 'sort_order'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * "Ангилалгүй" ангилал — байхгүй бол үүсгэнэ. Хэзээ ч устгагдахгүй.
     */
    public static function uncategorized(): self
    {
        return static::firstOrCreate(
            ['slug' => self::UNCATEGORIZED_SLUG],
            ['name' => 'Ангилалгүй', 'parent_id' => null, 'sort_order' => 999],
        );
    }

    /** Энэ нь хамгаалагдсан "Ангилалгүй" ангилал эсэх. */
    public function isUncategorized(): bool
    {
        return $this->slug === self::UNCATEGORIZED_SLUG;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NewsCategory::class, 'parent_id')->orderBy('sort_order');
    }
}
