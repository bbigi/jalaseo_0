<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $table = 'ane_general_settings';

    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Get default text AI providers (fallback chain)
     */
    public static function getDefaultTextAI(): array
    {
        return static::get('default_text_ai', ['gemini', 'deepseek']);
    }

    /**
     * Get default image AI providers (fallback chain)
     */
    public static function getDefaultImageAI(): array
    {
        return static::get('default_image_ai', ['flux']);
    }
}
