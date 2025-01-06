<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

/**
 * Instanciation du Router
 */
$router = new Router();

/**
 * Routes principales
 */
$router->get('/', 'HomeController@index')->setName('home');                // Accueil
$router->get('/games', 'GamesController@index')->setName('games');        // Liste des jeux
$router->get('/promotions', 'PromotionsController@index')->setName('promotions'); // Promotions
$router->get('/messages', 'GuestbookController@index')->setName('guestbook');     // Livre d'or
$router->get('/about', 'AboutController@index')->setName('about');        // À propos
$router->get('/contact', 'ContactController@index')->setName('contact');  // Contact (GET -> affichage)
$router->post('/contact', 'ContactController@send')->setName('contact_send');  // Contact (POST -> traitement form)

/**
 * Routes compte utilisateur (exemples)
 */
$router->get('/login', 'AccountController@login')->setName('login');         // Formulaire de connexion
$router->post('/login', 'AccountController@loginPost')->setName('login_post'); // Traitement connexion
$router->get('/register', 'AccountController@register')->setName('register');   // Formulaire d'inscription
$router->post('/register', 'AccountController@registerPost')->setName('register_post'); // Traitement inscription
$router->get('/logout', 'AccountController@logout')->setName('logout');       // Déconnexion
$router->get('/profile', 'AccountController@profile')->setName('profile');    // Profil utilisateur

/**
 * Route 404 si tu souhaites une page d'erreur dédiée
 * (Facultatif — dépend de la logique de ton Router)
 */
// $router->get('/404', 'ErrorController@notFound')->setName('error_404');

/**
 * Dispatch : exécuter la route correspondant à l'URL/méthode
 */
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
