<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;

class LoginController extends Controller
{

    public function login()
    {
        $error   = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        echo $this->twig->render('Auth/login.html.twig', [
            'error'      => $error ? $this->translateError($error) : null,
            'success'    => $success ? $this->translateSuccess($success) : null,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function loginPost()
    {
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$this->verifyCsrfToken($csrfToken)) {
            return $this->redirect('/login', ['error' => 'invalid_csrf']);
        }

        if (empty($email) || empty($password)) {
            return $this->redirect('/login', ['error' => 'empty_fields']);
        }

        $user = User::findByEmail($email, $this->pdo);
        if (!$user || !password_verify($password, $user->password)) {
            return $this->redirect('/login', ['error' => 'invalid_credentials']);
        }

        if (!$user->is_active) {
            return $this->redirect('/login', ['error' => 'inactive_account']);
        }

        $authService = new AuthService();
        $authService->login($user, $this->pdo);

        return $this->redirect('/profile', ['success' => 'login_success']);
    }
}
