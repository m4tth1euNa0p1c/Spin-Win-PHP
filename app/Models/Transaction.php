<?php

namespace App\Models;

use PDO;
use PDOException;

class Transaction
{
    public $id;
    public $user_id;
    public $type;
    public $amount;
    public $created_at;

    /**
     * @param PDO    $pdo
     * @param int    $userId
     * @param string $type
     * @param int    $amount
     * @return bool
     */
    public function save(PDO $pdo, int $userId, string $type, int $amount): bool
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (:user_id, :type, :amount)");
            return $stmt->execute([
                ':user_id' => $userId,
                ':type'    => $type,
                ':amount'  => $amount,
            ]);
        } catch (PDOException $e) {
            error_log("Failed to save transaction for user ID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param PDO $pdo
     * @param int $userId
     * @return array
     */
    public static function getTransactions(PDO $pdo, int $userId): array
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to retrieve transactions for user ID {$userId}: " . $e->getMessage());
            return [];
        }
    }
}
