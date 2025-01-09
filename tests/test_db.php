<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getConnection();
    echo "Connexion réussie à la base de données.";
} catch (Exception $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
