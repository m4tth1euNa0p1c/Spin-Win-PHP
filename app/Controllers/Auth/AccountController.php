<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class AccountController extends Controller
{

    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login', ['error' => 'not_logged_in']);
        }

        $user = User::findById($_SESSION['user_id'], $this->pdo);
        if (!$user) {
            return $this->redirect('/login', ['error' => 'user_not_found']);
        }
        $temp_message = $_GET['success'] ?? null;
        $error_message = $_GET['error'] ?? null;

        echo $this->twig->render('Account/profile.html.twig', [
            'includeNavbarAndFooter' => true,
            'username'     => htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'),
            'email'        => htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'),
            'role'         => htmlspecialchars($user->role, ENT_QUOTES, 'UTF-8'),
            'coins'        => $user->coins, // Passer les coins à la vue
            'temp_message' => $temp_message ? $this->translateSuccess($temp_message) : null,
            'error'        => $error_message ? $this->translateError($error_message) : null,
            'csrf_token'   => $this->generateCsrfToken(),
        ]);
    }


    public function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login', ['error' => 'not_logged_in']);
        }

        $user = User::findById($_SESSION['user_id'], $this->pdo);
        if (!$user) {
            return $this->redirect('/login', ['error' => 'user_not_found']);
        }

        $username         = trim($_POST['username'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $csrfToken        = $_POST['csrf_token'] ?? '';

        if (!$this->verifyCsrfToken($csrfToken)) {
            return $this->redirect('/profile', ['error' => 'invalid_csrf']);
        }

        if (empty($username) || empty($email)) {
            return $this->redirect('/profile', ['error' => 'empty_fields']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirect('/profile', ['error' => 'invalid_email']);
        }

        if (!empty($password)) {
            if ($password !== $confirm_password) {
                return $this->redirect('/profile', ['error' => 'password_mismatch']);
            }
            if (strlen($password) < 8) {
                return $this->redirect('/profile', ['error' => 'weak_password']);
            }
            $user->password = password_hash($password, PASSWORD_BCRYPT);
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id");
        $stmt->execute([
            ':email'    => $email,
            ':username' => $username,
            ':id'       => $user->id
        ]);
        if ($stmt->fetch()) {
            return $this->redirect('/profile', ['error' => 'email_or_username_exists']);
        }

        $user->username = $username;
        $user->email    = $email;

        if ($user->update($this->pdo)) { // Passer le PDO correctement
            return $this->redirect('/profile', ['success' => 'profile_updated']);
        } else {
            return $this->redirect('/profile', ['error' => 'update_failed']);
        }
    }
}
