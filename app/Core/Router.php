<?php
namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Gère une route GET
     */
    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Gère une route POST
     */
    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Gère une route PUT (facultatif)
     */
    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Gère une route DELETE (facultatif)
     */
    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Ajoute une route dans le tableau $routes
     */
    private function addRoute($method, $uri, $action)
    {
        $route = new Route($method, $uri, $action);
        $this->routes[] = $route;
        return $route; // Permet de faire ->setName('home') etc.
    }

    /**
     * Cherche et exécute la route correspondant à l'URL et la méthode HTTP
     */
    public function dispatch($uri, $method)
    {
        // On nettoie l'URI (supprime le slash final, gère le query string éventuel)
        $requestUri = rtrim(parse_url($uri, PHP_URL_PATH), '/');

        // On vérifie chaque route
        foreach ($this->routes as $route) {
            if ($route->matches($requestUri, $method)) {
                // Si la route matche, on exécute
                return $route->execute($requestUri);
            }
        }

        // Aucune route ne correspond : 404
        http_response_code(404);
        echo "404 Not Found";
        // OU BIEN, redirection vers une page /404 si tu as un contrôleur dédié :
        // header('Location: /404');
        // exit;
    }
}
