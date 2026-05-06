<?php

declare(strict_types=1);

namespace App\Services\Auth;

use RuntimeException;

final class JwtService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function encode(array $payload, string $secret): string
    {
        $header = self::base64UrlEncode((string) json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body = self::base64UrlEncode((string) json_encode($payload));
        $signature = self::base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        return "{$header}.{$body}.{$signature}";
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format.');
        }

        [$header, $body, $signature] = $parts;

        $expected = self::base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        if (! hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid JWT signature.');
        }

        $decoded = json_decode(self::base64UrlDecode($body), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid JWT payload.');
        }

        return $decoded;
    }

    private static function base64UrlEncode(string $data): string
    {
        return mb_rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid base64url encoding.');
        }

        return $decoded;
    }
}
