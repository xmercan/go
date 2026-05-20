<?php

namespace GO\Core;

use PDO;
use PDOException;

/**
 * PDO Singleton bağlantısı.
 * Tüm database işlemleri bu sınıf üzerinden yapılır.
 */
class Database
{
    private static ?PDO $connection = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * PDO bağlantısını al (ya da oluştur).
     */
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = self::createConnection();
        }
        return self::$connection;
    }

    /**
     * PDO bağlantısı oluştur.
     *
     * @throws PDOException Bağlantı kurulamazsa
     */
    private static function createConnection(): PDO
    {
        $host    = defined('DB_HOST')    ? DB_HOST    : 'localhost';
        $port    = defined('DB_PORT')    ? DB_PORT    : 3306;
        $name    = defined('DB_NAME')    ? DB_NAME    : '';
        $user    = defined('DB_USER')    ? DB_USER    : '';
        $pass    = defined('DB_PASS')    ? DB_PASS    : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET time_zone = '+03:00'"); // Türkiye
            return $pdo;
        } catch (PDOException $e) {
            // Üretimde detaylı hata gösterme
            if (App::isDebug()) {
                throw $e;
            }
            throw new PDOException('Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisiyle iletişime geçin.');
        }
    }

    /**
     * getInstance() — connection() için alias (geriye dönük uyumluluk).
     */
    public static function getInstance(): PDO
    {
        return self::connection();
    }

    /**
     * Bağlantıyı sıfırla (test veya fork sonrası).
     */
    public static function reset(): void
    {
        self::$connection = null;
    }

    /**
     * Bağlantı testi — install sihirbazı için.
     */
    public static function test(string $host, int $port, string $name, string $user, string $pass): bool
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return true;
        } catch (PDOException) {
            return false;
        }
    }
}
