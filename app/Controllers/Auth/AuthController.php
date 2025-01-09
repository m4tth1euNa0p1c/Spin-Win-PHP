<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $user = User::findById($_SESSION['user_id'], $this->pdo);
            if ($user) {
                $authService = new AuthService();
                $authService->logout($user, $this->pdo);
            }
        }
        return $this->redirect('/login', ['success' => 'logout_success']);
    }
}
