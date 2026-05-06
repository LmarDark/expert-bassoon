<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AppFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $api_key
 * @property list<string> $allowed_domains
 * @property string|null $callback_url
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class App extends Model
{
    /** @use HasFactory<AppFactory> */
    use HasFactory;

    protected $fillable = ['name', 'api_key', 'allowed_domains', 'callback_url', 'active'];

    public static function generateApiKey(): string
    {
        return Str::random(64);
    }

    /** @return HasMany<SsoToken, $this> */
    public function ssoTokens(): HasMany
    {
        return $this->hasMany(SsoToken::class);
    }

    public function isAllowedDomain(string $host): bool
    {
        foreach ($this->allowed_domains as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allowed_domains' => 'array',
            'active' => 'boolean',
        ];
    }
}
