<?php

namespace App\Models;

use PDO;
use PDOException;

class User
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $is_active;
    public $role;
    public $coins;
    public $remember_token;

    /**
     * @param PDO $pdo
     * @return void
     */
    public function save(PDO $pdo): void
    {
        if ($this->id) {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, password = :password, coins = :coins, is_active = :is_active, role = :role, remember_token = :remember_token WHERE id = :id");
            $stmt->execute([
                ':username'       => $this->username,
                ':email'          => $this->email,
                ':password'       => $this->password,
                ':coins'          => $this->coins,
                ':is_active'      => $this->is_active,
                ':role'           => $this->role,
                ':remember_token' => $this->remember_token,
                ':id'             => $this->id,
            ]);
        } else {
            // Insertion
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, coins, is_active, role, remember_token) VALUES (:username, :email, :password, :coins, :is_active, :role, :remember_token)");
            $stmt->execute([
                ':username'       => $this->username,
                ':email'          => $this->email,
                ':password'       => $this->password,
                ':coins'          => $this->coins,
                ':is_active'      => $this->is_active,
                ':role'           => $this->role,
                ':remember_token' => $this->remember_token,
            ]);
            $this->id = $pdo->lastInsertId();
        }
    }

    /**
     * @param PDO $pdo
     * @return bool
     */
    public function update(PDO $pdo): bool
    {
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, password = :password, coins = :coins, is_active = :is_active, role = :role, remember_token = :remember_token WHERE id = :id");
            return $stmt->execute([
                ':username'       => $this->username,
                ':email'          => $this->email,
                ':password'       => $this->password,
                ':coins'          => $this->coins,
                ':is_active'      => $this->is_active,
                ':role'           => $this->role,
                ':remember_token' => $this->remember_token,
                ':id'             => $this->id,
            ]);
        } catch (PDOException $e) {
            error_log("Failed to update user ID {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param string $email
     * @param PDO    $pdo
     * @return self|null
     */
    public static function findByEmail(string $email, PDO $pdo): ?self
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $user = new self();
            $user->id             = $data['id'];
            $user->username       = $data['username'];
            $user->email          = $data['email'];
            $user->password       = $data['password'];
            $user->coins          = $data['coins'];
            $user->is_active      = $data['is_active'];
            $user->role           = $data['role'];
            $user->remember_token = $data['remember_token'];
            return $user;
        }

        return null;
    }

    /**
     * @param int  $id
     * @param PDO  $pdo
     * @return self|null
     */
    public static function findById(int $id, PDO $pdo): ?self
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $user = new self();
            $user->id             = $data['id'];
            $user->username       = $data['username'];
            $user->email          = $data['email'];
            $user->password       = $data['password'];
            $user->coins          = $data['coins'];
            $user->is_active      = $data['is_active'];
            $user->role           = $data['role'];
            $user->remember_token = $data['remember_token'];
            return $user;
        }

        return null;
    }

    /**
     * @param PDO $pdo
     * @param int $amount
     * @return bool
     */
    public function deductCoins(PDO $pdo, int $amount): bool
    {
        if ($this->coins >= $amount) {
            $this->coins -= $amount;
            return $this->update($pdo);
        }
        return false;
    }

    /**
     * @param PDO $pdo
     * @param int $amount
     * @return bool
     */
    public function addCoins(PDO $pdo, int $amount): bool
    {
        $this->coins += $amount;
        return $this->update($pdo);
    }
}
