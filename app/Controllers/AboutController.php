<?php
namespace App\Controllers;

use App\Core\Controller;

class AboutController extends Controller
{
    public function index()
    {
        // Rendu Twig
        echo $this->twig->render('Pages/About/index.html.twig', [
            'title' => 'About',
            'includeNavbarAndFooter' => true,
            'app_user' => $this->getCurrentUser(),
        ]);
    }

    private function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }
}
