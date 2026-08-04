<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TaxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'display_name',
        'category',
        'type',
    ];

    /**
     * Get a setting value by its key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = Cache::rememberForever("tax_setting.{$key}", function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'percentage' => (float) $setting->value,
            'amount' => (float) $setting->value,
            'number' => (float) $setting->value,
            default => $setting->value,
        };
    }

    /**
     * Clear the cache when saved or deleted.
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget("tax_setting.{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("tax_setting.{$setting->key}");
        });
    }
}
