<?php

namespace App\Core;

class Session {
    protected const FLASH_KEY = 'flash_messages';

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }

        // Handle session timeout
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            self::destroy();
        }
        $_SESSION['LAST_ACTIVITY'] = time();

        self::cleanFlashMessages();
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function setFlash(string $key, string $message, string $type = 'success'): void {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message,
            'type' => $type
        ];
    }

    public static function getFlash(string $key): ?array {
        $flash = $_SESSION[self::FLASH_KEY][$key] ?? null;
        if ($flash) {
            unset($_SESSION[self::FLASH_KEY][$key]);
        }
        return $flash;
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    protected static function cleanFlashMessages(): void {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => $flashMessage) {
            if ($flashMessage['remove'] ?? false) {
                unset($flashMessages[$key]);
            }
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}
