<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /** Тохиргооны утгыг авах (cache-тэй). */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', fn () => static::pluck('value', 'key')->all());

        return $all[$key] ?? $default;
    }

    public static function boolean(string $key, bool $default = true): bool
    {
        $v = static::get($key);

        return $v === null ? $default : filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    /** Тохиргоог хадгалаад cache-ийг шинэчилнэ. */
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]);
        Cache::forget('settings.all');
    }
}
