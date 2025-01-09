<?php
namespace App\Controllers\About;

use App\Core\Controller;

class AboutController extends Controller
{
    public function index()
    {
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
