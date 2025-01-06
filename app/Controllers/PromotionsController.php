<?php
namespace App\Controllers;

use App\Core\Controller;

class PromotionsController extends Controller
{
    public function index()
    {
        echo $this->twig->render('Pages/Promotions/index.html.twig', [
            'title' => 'Promotions',
            'includeNavbarAndFooter' => true,
            'app_user' => $this->getCurrentUser(),
        ]);
    }

    private function getCurrentUser()
    {
        // Par exemple, renvoie l'user depuis la session
        return $_SESSION['user'] ?? null;
    }
}
