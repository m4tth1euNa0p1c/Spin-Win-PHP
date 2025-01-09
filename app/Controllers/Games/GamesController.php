<?php
namespace App\Controllers\Games;

use App\Core\Controller;

class GamesController extends Controller
{
    public function index()
    {
        echo $this->twig->render('Pages/Games/index.html.twig', [
            'title' => 'Nos Jeux',
            'includeNavbarAndFooter' => true,
            'app_user' => $this->getCurrentUser(),
        ]);
    }

    private function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }
}
