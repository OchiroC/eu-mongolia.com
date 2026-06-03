<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionalCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }
}
