<?php
namespace App\Core\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Permet d'utiliser {{ path('some_route') }} dans Twig,
 * en se basant sur un tableau associatif routeName => URL.
 */
class PathExtension extends AbstractExtension
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'generatePath']),
        ];
    }

    /**
     * Retourne l'URL correspondant au nom de route $routeName
     */
    public function generatePath(string $routeName): string
    {
        return $this->routes[$routeName] ?? '#';
    }
}
