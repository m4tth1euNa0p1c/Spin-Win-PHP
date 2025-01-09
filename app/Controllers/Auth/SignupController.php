<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;

class SignupController extends Controller
{

    public function register()
    {
        $error   = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        echo $this->twig->render('Auth/signup.html.twig', [
            'error'      => $error ? $this->translateError($error) : null,
            'success'    => $success ? $this->translateSuccess($success) : null,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }


    public function registerPost()
    {
        $email            = trim($_POST['email'] ?? '');
        $username         = trim($_POST['username'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $csrfToken        = $_POST['csrf_token'] ?? '';

        if (!$this->verifyCsrfToken($csrfToken)) {
            return $this->redirect('/register', ['error' => 'invalid_csrf']);
        }

        if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
            return $this->redirect('/register', ['error' => 'empty_fields']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirect('/register', ['error' => 'invalid_email']);
        }

        if ($password !== $confirm_password) {
            return $this->redirect('/register', ['error' => 'password_mismatch']);
        }

        if (strlen($password) < 8) {
            return $this->redirect('/register', ['error' => 'weak_password']);
        }

        $existingUser = User::findByEmail($email, $this->pdo);
        if ($existingUser) {
            return $this->redirect('/register', ['error' => 'email_exists']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User();
        $user->username    = $username;
        $user->email       = $email;
        $user->password    = $hashedPassword;
        $user->coins       = 100;
        $user->is_active   = 1;
        $user->role        = 'user';
        $user->save($this->pdo);

        $authService = new AuthService();
        $authService->login($user, $this->pdo);

        return $this->redirect('/profile', ['success' => 'registration_success']);
    }
}
