<?php

namespace App\Framework;

use App\Config;

class JwtHelper
{
    public static function encode(array $payload): string
    {
        $header    = self::b64(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload   = self::b64(json_encode($payload));
        $signature = self::b64(hash_hmac('sha256', "$header.$payload", Config::JWT_SECRET, true));
        return "$header.$payload.$signature";
    }

    public static function decode(string $token): object
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new \Exception('Invalid token');

        [$h, $p, $sig] = $parts;
        $expected = self::b64(hash_hmac('sha256', "$h.$p", Config::JWT_SECRET, true));

        if (!hash_equals($expected, $sig)) throw new \Exception('Invalid signature');

        $data = json_decode(base64_decode(strtr($p, '-_', '+/')));
        if (!$data || $data->exp < time()) throw new \Exception('Token expired');

        return $data;
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}