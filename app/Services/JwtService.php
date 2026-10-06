<?php

namespace App\Services;

class JwtService {
    public static function generateToken(array $payload): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;
        $payloadJson = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verifyToken(string $jwt): ?array {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return null;
        }

        $header = self::base64UrlDecode($tokenParts[0]);
        $payload = self::base64UrlDecode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        if (!hash_equals($base64UrlSignature, $signatureProvided)) {
            return null;
        }

        $data = json_decode($payload, true);
        if (($data['exp'] ?? 0) < time()) {
            return null;
        }

        return $data;
    }

    private static function base64UrlEncode(string $text): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    private static function base64UrlDecode(string $text): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $text));
    }
}
