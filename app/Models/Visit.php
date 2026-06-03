<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $timestamps = false; // created_at-ыг гараар (app timezone) онооно

    protected $fillable = ['session_id', 'ip', 'path', 'user_id', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
