<?php

namespace App\Controllers\Home;

use App\Core\Controller;
use PharIo\Manifest\Application;

class HomeController extends Controller
{

    

    public function index()
    {

        $slides = [
            [
                'image' => '/images/image.png',
                'icon' => 'fas fa-coins',
                'title' => 'Spin&Win 🎰 - Casion en Ligne',
                'description' => "Plongez dans le développement d'un casino en ligne en PHP procédural.",
            ],
            [
                'image' => '/images/image.png',
                'icon' => 'fas fa-user-friends',
                'title' => 'Connexion et Déconnexion simplifiées 😎',
                'description' => "Offrez à vos utilisateurs la possibilité de créer un compte avec un nom d'utilisateur unique et un mot de passe sécurisé, Vous acces a 60 coins pour jouer.",
            ],
            [
                'image' => '/images/image.png',
                'icon' => 'fas fa-clock',
                'title' => 'Un affichage des gains en temps réel 🕒',
                'description' => "Affichez les gains en temps réel pour que les utilisateurs puissent voir combien ils ont gagné.",
            ],
        ];

        echo $this->twig->render('Pages/Home/index.html.twig', [
            'title' => 'Accueil - Spin & Win 🎰',
            'includeNavbarAndFooter' => true,
            'app_user' => $this->getCurrentUser(),
            'slides' => $slides,
        ]);

        
    }

    private function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }
}
