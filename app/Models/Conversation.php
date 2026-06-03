<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['listing_id', 'buyer_id', 'seller_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** Тухайн хэрэглэгч энэ ярианд оролцогч эсэх. */
    public function hasParticipant(int $userId): bool
    {
        return $this->buyer_id === $userId || $this->seller_id === $userId;
    }

    /** Нөгөө талын хэрэглэгч. */
    public function otherUser(int $userId): ?User
    {
        return $userId === $this->buyer_id ? $this->seller : $this->buyer;
    }

    /** Тухайн хэрэглэгчид уншаагүй мессежийн тоо. */
    public function unreadFor(int $userId): int
    {
        return $this->messages()->whereNull('read_at')->where('sender_id', '!=', $userId)->count();
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('buyer_id', $userId)->orWhere('seller_id', $userId);
    }
}
