<?php
namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    /**
     * Affiche la page 404
     * GET /404
     */
    public function notFound()
    {
        http_response_code(404);
        echo $this->twig->render('Errors/404.html.twig');
    }
}
