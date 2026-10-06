<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Config\Database as DBConfig;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = DBConfig::getCredentials();
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function beginTransaction(): void {
        if (!self::getInstance()->inTransaction()) {
            self::getInstance()->beginTransaction();
        }
    }

    public static function commit(): void {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->commit();
        }
    }

    public static function rollBack(): void {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->rollBack();
        }
    }
}
