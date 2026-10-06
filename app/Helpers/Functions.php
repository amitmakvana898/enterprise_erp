<?php

use App\Core\Session;
use App\Helpers\Security;

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/enterprise_erp';
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/public') !== false) {
            $baseUrl = rtrim($baseUrl, '/') . '/public';
        }
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/enterprise_erp';
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/public') !== false) {
            return rtrim($baseUrl, '/') . '/public/assets/' . ltrim($path, '/');
        }
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(?string $str): string {
        return Security::sanitize($str ?? '');
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, string $symbol = '₹'): string {
        return $symbol . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array {
        $user = Session::get('user');
        if ($user === null) {
            return null;
        }
        if (is_object($user)) {
            return (array)$user;
        }
        return is_array($user) ? $user : null;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return Session::get('user') !== null;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permissionCode): bool {
        $user = auth_user();
        if (!$user) return false;
        if (($user['role_name'] ?? '') === 'super_admin') return true;

        $permissions = Session::get('user_permissions', []);
        return in_array($permissionCode, $permissions);
    }
}

if (!function_exists('is_active')) {
    function is_active(string $path): string {
        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $uri = parse_url($uri, PHP_URL_PATH) ?? '';
            $target = '/' . trim($path, '/');

            if ($target === '/dashboard' || $target === '/') {
                if ($uri === '/' || $uri === '' || str_ends_with($uri, '/public') || str_ends_with($uri, '/public/') || str_contains($uri, '/dashboard')) {
                    return 'active';
                }
                return '';
            }

            return str_contains($uri, $target) ? 'active' : '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Security::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

