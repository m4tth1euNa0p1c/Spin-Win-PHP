<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        $error   = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        echo $this->twig->render('Auth/forgot_password.html.twig', [
            'error'      => $error ? $this->translateError($error) : null,
            'success'    => $success ? $this->translateSuccess($success) : null,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function handleForgotPassword()
    {
        $email     = trim($_POST['email'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$this->verifyCsrfToken($csrfToken)) {
            return $this->redirect('/forgot_password', ['error' => 'invalid_csrf']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirect('/forgot_password', ['error' => 'invalid_email']);
        }

        $user = User::findByEmail($email, $this->pdo);
        if (!$user) {
            return $this->redirect('/forgot_password', ['error' => 'user_not_found']);
        }

        $token = bin2hex(random_bytes(50));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $_SESSION['password_reset'] = [
            'token'      => $token,
            'user_id'    => $user->id,
            'expires_at' => $expiresAt,
        ];

        $queryParams = http_build_query([
            'token'   => $token,
            'user_id' => $user->id,
            'success' => 'reset_email_sent',
        ]);
        return $this->redirect("/reset_password?$queryParams");
    }

    public function showResetPasswordForm()
    {
        $token    = $_GET['token']    ?? '';
        $userId   = $_GET['user_id']  ?? '';
        $error    = $_GET['error']    ?? null;
        $success  = $_GET['success']  ?? null;

        echo $this->twig->render('Auth/reset_password.html.twig', [
            'error'       => $error ? $this->translateError($error) : null,
            'success'     => $success ? $this->translateSuccess($success) : null,
            'token'       => $token,
            'user_id'     => $userId,
            'csrf_token'  => $this->generateCsrfToken(),
        ]);
    }

    public function handleResetPassword()
    {
        $token            = $_POST['token']              ?? '';
        $userId           = $_POST['user_id']            ?? '';
        $newPassword      = $_POST['password']           ?? '';
        $confirmPassword  = $_POST['confirm_password']   ?? '';
        $csrfToken        = $_POST['csrf_token']         ?? '';

        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        if (!$userId || !$token || !$newPassword || !$confirmPassword) {
            return $this->redirect("/reset_password?" . http_build_query([
                'token'   => $token,
                'user_id' => $userId,
                'error'   => 'missing_data'
            ]));
        }

        if (!$this->verifyCsrfToken($csrfToken)) {
            return $this->redirect("/reset_password?" . http_build_query([
                'token'   => $token,
                'user_id' => $userId,
                'error'   => 'invalid_csrf'
            ]));
        }

        if ($newPassword !== $confirmPassword) {
            return $this->redirect("/reset_password?" . http_build_query([
                'token'   => $token,
                'user_id' => $userId,
                'error'   => 'password_mismatch'
            ]));
        }

        if (strlen($newPassword) < 8) {
            return $this->redirect("/reset_password?" . http_build_query([
                'token'   => $token,
                'user_id' => $userId,
                'error'   => 'weak_password'
            ]));
        }

        $user = User::findById($userId, $this->pdo);
        if ($user) {
            $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
            $user->save($this->pdo);

            unset($_SESSION['password_reset']);

            return $this->redirect("/login", ['success' => 'password_reset_success']);
        } else {
            return $this->redirect('/forgot_password', ['error' => 'user_not_found']);
        }
    }
}