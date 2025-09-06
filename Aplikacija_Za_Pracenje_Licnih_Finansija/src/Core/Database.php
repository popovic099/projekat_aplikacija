<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    // Singleton pattern za konekciju
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
                $port = defined('DB_PORT') ? DB_PORT : '3306';
                $name = defined('DB_NAME') ? DB_NAME : 'finance_tracker';
                $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
                self::$instance = new PDO($dsn, DB_USER, DB_PASS);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                die("Greška konekcije: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Zabrani kloniranje
    private function __clone() {}

    // Zabrani deserijalizaciju
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
