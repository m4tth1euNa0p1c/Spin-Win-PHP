<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Controller
{
    protected $twig;

    public function __construct()
    {
        // Indique à Twig où se trouvent tous les templates
        $loader = new FilesystemLoader([
            __DIR__ . '/../../src/Frontend/Templates',
        ]);

        // Active l'environnement Twig
        $this->twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../cache/twig', // false pour désactiver le cache en dev
            'debug' => true,
        ]);

        // Extension debug (facultatif)
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }
}
