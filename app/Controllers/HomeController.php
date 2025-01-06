<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        echo $this->twig->render('Pages/Home/index.html.twig', [
            'title' => 'Acceuil - Spin & Win 🎰',
            'includeNavbarAndFooter' => true, // IMPORTANT
            'app_user' => $this->getCurrentUser(),
        ]);
    }

    private function getCurrentUser()
    {
        // Par exemple, renvoie l'user depuis la session
        return $_SESSION['user'] ?? null;
    }
}
