<?php
namespace App\Core;

use Twig\Environment;
use PDO;

class Router
{
    private $routes = [];

    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute($method, $uri, $action)
    {
        $route = new Route($method, $uri, $action);
        $this->routes[] = $route;
        return $route; // Permet de faire ->setName('home') etc.
    }

    public function dispatch($uri, $method, Environment $twig, PDO $pdo)
    {
        $requestUri = rtrim(parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes as $route) {
            if ($route->matches($requestUri, $method)) {
                return $route->execute($twig, $pdo, $requestUri);
            }
        }

        http_response_code(404);
        echo $twig->render('Errors/404.html.twig');
    }
}
