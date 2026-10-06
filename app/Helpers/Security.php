<?php

namespace App\Helpers;

use App\Core\Session;

class Security {
    public static function generateCsrfToken(): string {
        if (!Session::get('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function validateCsrfToken(?string $token): bool {
        $sessionToken = Session::get('csrf_token');
        return $token && $sessionToken && hash_equals($sessionToken, $token);
    }

    public static function sanitize(string $data): string {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
