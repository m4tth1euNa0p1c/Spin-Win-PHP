<?php
namespace App\Controllers;

use App\Core\Controller;

class ContactController extends Controller
{
    public function index()
    {
        echo $this->twig->render('Pages/Contact/index.html.twig', [
            'title' => 'Contact',
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
