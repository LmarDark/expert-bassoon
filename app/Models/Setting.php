<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $key
 * @property string|null $value
 */
final class Setting extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::query()->where('key', $key)->value('value');

        return is_string($value) ? $value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** @return array{type: string, custom_pattern: string} */
    public static function usernameValidation(): array
    {
        $raw = self::whereIn('key', ['username_validation_type', 'username_custom_pattern'])
            ->pluck('value', 'key');

        return [
            'type' => $raw->get('username_validation_type') ?? '',
            'custom_pattern' => $raw->get('username_custom_pattern') ?? '',
        ];
    }

    /** @return array{app_name: string, show_logo: bool, primary_color: string, custom_css: string, logo_url: string|null, bg_color: string} */
    public static function loginSettings(): array
    {
        $raw = self::whereIn('key', [
            'login_app_name',
            'login_show_logo',
            'login_primary_color',
            'login_custom_css',
            'login_logo_path',
            'login_bg_color',
        ])->pluck('value', 'key');

        $logoPath = $raw->get('login_logo_path');

        return [
            'app_name' => $raw->get('login_app_name') ?? 'Sistema de Autenticação',
            'show_logo' => ($raw->get('login_show_logo') ?? '1') !== '0',
            'primary_color' => $raw->get('login_primary_color') ?? '#F53003',
            'custom_css' => $raw->get('login_custom_css') ?? '',
            'logo_url' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
            'bg_color' => $raw->get('login_bg_color') ?? '',
        ];
    }
}
