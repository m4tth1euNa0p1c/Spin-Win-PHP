<?php
namespace App\Services;

use App\Models\User;
use PDO;

class AuthService
{
    /**
     * Gère la connexion de l'utilisateur
     *
     * @param User $user
     * @param PDO  $pdo
     * @return void
     */
    public function login(User $user, PDO $pdo): void
    {
        // Regénérer l'ID de session pour prévenir les attaques de fixation de session
        session_regenerate_id(true);

        // Stocker l'ID utilisateur dans la session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;

        // Optionnel : Gérer un "Remember Me" via un cookie
        // $this->createRememberToken($user, $pdo);
    }

    /**
     * Gère la déconnexion de l'utilisateur
     *
     * @param User $user
     * @param PDO  $pdo
     * @return void
     */
    public function logout(User $user, PDO $pdo): void
    {
        // Optionnel : Supprimer le token "Remember Me"
        // $this->deleteRememberToken($user, $pdo);

        // Détruire toutes les données de session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Optionnel : Créer un token "Remember Me"
     *
     * @param User $user
     * @param PDO  $pdo
     * @return void
     */
    private function createRememberToken(User $user, PDO $pdo): void
    {
        $token = bin2hex(random_bytes(16));
        // $user->remember_token = $token;
        // $user->save($pdo);

        // setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true); // 30 jours
    }

    /**
     * Optionnel : Supprimer le token "Remember Me"
     *
     * @param User $user
     * @param PDO  $pdo
     * @return void
     */
    private function deleteRememberToken(User $user, PDO $pdo): void
    {
        // $user->remember_token = null;
        // $user->save($pdo);

        // setcookie('remember_token', '', time() - 3600, "/", "", true, true);
    }
}
