<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ride extends Model
{
    protected $fillable = [
        'user_id', 'from_city', 'from_country', 'to_city', 'to_country',
        'depart_at', 'seats', 'price', 'notes', 'contact_phone', 'status',
    ];

    protected $casts = ['depart_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Хараахан болоогүй (хугацаа нь өнгөрөөгүй) аяллууд. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('depart_at', '>=', now()->startOfDay());
    }
}
