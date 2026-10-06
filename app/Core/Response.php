<?php

namespace App\Core;

class Response {
    public function setStatusCode(int $code): void {
        http_response_code($code);
    }

    public function redirect(string $url): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: " . $url);
        exit;
    }

    public function json(array $data, int $statusCode = 200): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
