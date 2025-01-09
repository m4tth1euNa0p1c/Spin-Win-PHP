<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $pdo;

    public static function getConnection()
    {
        if (!self::$pdo) {
            $config = require __DIR__ . '/../../config/database.php';

            $host     = $config['host'];
            $port     = $config['port'];
            $dbname   = $config['database'];
            $username = $config['username'];
            $password = $config['password'];
            $charset  = $config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
