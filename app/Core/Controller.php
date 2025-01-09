<?php
namespace App\Core;

use Twig\Environment;

class Controller
{
    protected $twig;
    protected $pdo;

    /**
     * @param Environment $twig Instance de Twig
     * @param \PDO        $pdo  Instance de PDO pour la base de données
     */
    public function __construct(Environment $twig, \PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo  = $pdo;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @param string $url    L'URL de redirection (ex: '/login')
     * @param array  $params (facultatif) Paramètres GET
     */
    protected function redirect(string $url, array $params = []): void
    {
        if (!empty($params)) {
            $query = http_build_query($params);
            $url   .= '?' . $query;
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * @return string Le token CSRF généré
     */
    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * @param string $token Le token CSRF à vérifier
     *
     * @return bool True si le token est valide, sinon False
     */
    protected function verifyCsrfToken(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * @param string $error_code Le code d'erreur à traduire
     *
     * @return string Le message d'erreur traduit
     */
    protected function translateError(string $error_code): string
    {
        $errors = [
            'empty_fields'             => 'Tous les champs sont requis.',
            'invalid_csrf'             => 'Requête invalide. Veuillez réessayer.',
            'invalid_credentials'      => 'Identifiants invalides.',
            'inactive_account'         => 'Votre compte est inactif. Veuillez contacter l\'administrateur.',
            'email_exists'             => 'Cet email est déjà utilisé.',
            'invalid_email'            => 'Adresse email invalide.',
            'weak_password'            => 'Le mot de passe doit contenir au moins 8 caractères.',
            'not_logged_in'            => 'Veuillez vous connecter pour accéder à votre profil.',
            'user_not_found'           => 'Utilisateur introuvable.',
            'password_mismatch'        => 'Les mots de passe ne correspondent pas.',
            'email_or_username_exists' => 'Email ou nom d\'utilisateur déjà utilisé.',
            'update_failed'            => 'La mise à jour du profil a échoué. Veuillez réessayer.',
            'not_logged_for_play'      => 'Connecter vous pour jouer',
        ];

        return $errors[$error_code] ?? 'Une erreur inconnue est survenue.';
    }

    /**
     * @param string $success_code Le code de succès à traduire
     *
     * @return string Le message de succès traduit
     */
    protected function translateSuccess(string $success_code): string
    {
        $successes = [
            'login_success'        => 'Connexion réussie !',
            'registration_success' => 'Inscription réussie et vous êtes maintenant connecté.',
            'profile_updated'      => 'Profil mis à jour avec succès.',
            'logout_success'       => 'Déconnexion réussie.',
            'password_reset_success' => 'Mot de passe réinitialisé avec succès.',
        ];

        return $successes[$success_code] ?? 'Action réussie.';
    }
}
