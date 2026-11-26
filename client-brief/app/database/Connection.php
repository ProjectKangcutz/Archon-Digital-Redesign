<?php
declare(strict_types=1);

final class Connection
{
    private static ?PDO $instance = null;

    public static function getPdo(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $db = app_config('db');
        if (empty($db['name']) || empty($db['user'])) {
            throw new RuntimeException('Konfigurasi database belum lengkap.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['name'],
            $db['charset']
        );

        self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);

        return self::$instance;
    }
}
