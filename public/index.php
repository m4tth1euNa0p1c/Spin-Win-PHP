<?php
session_start();

// Charger l'autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Twig\PathExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use App\Core\Database;

// Charger les configurations depuis config/database.php
$config = require __DIR__ . '/../config/database.php';

// Initialiser Twig
$loader = new FilesystemLoader(__DIR__ . '/../src/Frontend/Templates');
$twig = new Environment($loader, [
    'cache' => false, // Désactiver le cache en développement
    'debug' => true,  // Activer le mode debug
]);

// Ajouter l'extension Debug de Twig (optionnel mais recommandé en dev)
$twig->addExtension(new DebugExtension());

// Ajouter les variables globales
$twig->addGlobal('session', $_SESSION);

// Définir les routes nommées
$namedRoutes = [
    'home'                   => '/',
    'games'                  => '/games',
    'promotions'             => '/promotions',
    'about'                  => '/about',
    'contact'                => '/contact',
    'contact_send'           => '/contact',
    'login'                  => '/login',
    'login_post'             => '/login',
    'register'               => '/register',
    'register_post'          => '/register',
    'logout'                 => '/logout',
    'forgot_password'        => '/forgot_password',
    'handle_forgot_password' => '/forgot_password',
    'reset_password'         => '/reset_password',
    'handle_reset_password'  => '/reset_password',
    'profile'                => '/profile',
    'update_profile'         => '/profile/update',
    'users'                  => '/users',
    'games_slots'            => '/games/slots',
    'games_slots_spin'       => '/games/slots/spin',
];

// Ajouter l'extension `PathExtension` à Twig
$twig->addExtension(new PathExtension($namedRoutes));

// Créer une instance du Router
$router = new Router();

// Définir les routes principales
$router->get('/', 'Home\HomeController@index')->setName('home');
$router->get('/games', 'Games\GamesController@index')->setName('games');
$router->get('/promotions', 'Promotions\PromotionsController@index')->setName('promotions');
$router->get('/about', 'About\AboutController@index')->setName('about');
$router->get('/contact', 'Contact\ContactController@index')->setName('contact');
$router->post('/contact', 'Contact\ContactController@send')->setName('contact_send');

// Routes pour la gestion des comptes utilisateurs
$router->get('/login', 'Auth\LoginController@login')->setName('login');
$router->post('/login', 'Auth\LoginController@loginPost')->setName('login_post');
$router->get('/register', 'Auth\SignupController@register')->setName('register');
$router->post('/register', 'Auth\SignupController@registerPost')->setName('register_post');
$router->get('/logout', 'Auth\AuthController@logout')->setName('logout');
$router->get('/forgot_password', 'Auth\PasswordResetController@showForgotPasswordForm')->setName('forgot_password');
$router->post('/forgot_password', 'Auth\PasswordResetController@handleForgotPassword')->setName('handle_forgot_password');
$router->get('/reset_password', 'Auth\PasswordResetController@showResetPasswordForm')->setName('reset_password');
$router->post('/reset_password', 'Auth\PasswordResetController@handleResetPassword')->setName('handle_reset_password');
$router->get('/profile', 'Auth\AccountController@profile')->setName('profile');
$router->post('/profile/update', 'Auth\AccountController@updateProfile')->setName('update_profile');

// Routes Jeu
$router->get('/games/slots', 'Games\Slot\SlotGamesController@slots')->setName('games_slots');
$router->post('/games/slots/spin', 'Games\Slot\SlotGamesController@spin')->setName('games_slots_spin');

// Routes Ultra Gains
$router->get('/games/ultra-gains', 'Games\UltraGains\UltraGainsController@ultraGains')->setName('games_ultra_gains');
$router->post('/games/ultra-gains/spin', 'Games\UltraGains\UltraGainsController@spin')->setName('games_ultra_gains_spin');

// Nouvelle route pour afficher la liste des utilisateurs
$router->get('/users', 'User\UserController@list')->setName('users');

// Route 404 (optionnelle)
$router->get('/404', 'ErrorController@notFound')->setName('error_404');

// Créer une instance de la base de données
try {
    $pdo = Database::getConnection();
} catch (\PDOException $e) {
    http_response_code(500);
    echo $twig->render('Errors/database_error.html.twig', ['message' => $e->getMessage()]);
    exit;
}

// Dispatcher la route actuelle avec les dépendances
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $twig, $pdo);
