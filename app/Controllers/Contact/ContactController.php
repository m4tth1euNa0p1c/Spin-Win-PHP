<?php
namespace App\Controllers\Contact;

use App\Core\Controller;

class ContactController extends Controller
{
    public function index()
    {
        echo $this->twig->render('Pages/Contact/index.html.twig', [
            'title' => 'Contact',
            'includeNavbarAndFooter' => true,
            'app_user' => $this->getCurrentUser(),
        ]);
    }

    private function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }
}
