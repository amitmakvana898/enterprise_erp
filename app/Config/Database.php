<?php

namespace App\Config;

class Database {
    public static function getCredentials(): array {
        return [
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'enterprise_erp',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ];
    }
}
