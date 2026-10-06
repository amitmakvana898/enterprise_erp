<?php

namespace App\Core;

class Request {
    public function getMethod(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $rootDir = preg_replace('#/public$#', '', $scriptDir);
        
        if (!empty($scriptDir) && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        } elseif (!empty($rootDir) && $rootDir !== '/' && strpos($uri, $rootDir) === 0) {
            $uri = substr($uri, strlen($rootDir));
        }

        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, 7);
        }

        return '/' . trim($uri, '/');
    }

    public function isPost(): bool {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool {
        return $this->getMethod() === 'GET';
    }

    public function getBody(): array {
        $body = [];
        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = is_array($value) ? $value : filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $body[$key] = $value;
                } else {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        $jsonInput = file_get_contents('php://input');
        if (!empty($jsonInput)) {
            $jsonData = json_decode($jsonInput, true);
            if (is_array($jsonData)) {
                $body = array_merge($body, $jsonData);
            }
        }

        return $body;
    }

    public function get(string $key, $default = null) {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }

    public function getHeader(string $header): ?string {
        $header = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        return $_SERVER[$header] ?? null;
    }
}
