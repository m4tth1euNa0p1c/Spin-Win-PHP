<?php
namespace App\Core;

class Route
{
    private $method;
    private $uri;
    private $action;
    private $name;
    private $paramsPattern; // Optionnel si tu veux supporter des paramètres dynamiques

    public function __construct($method, $uri, $action)
    {
        $this->method = $method;
        // Supprime le slash final
        $this->uri = rtrim($uri, '/');
        $this->action = $action;

        // Si tu veux supporter des paramètres dynamiques (ex: /games/{slug})
        // tu peux transformer {slug} en (?P<slug>[^/]+), etc.
        // $this->paramsPattern = $this->convertUriToRegex($this->uri);
    }

    /**
     * Nommer la route (facultatif)
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Vérifie si la route correspond à l'URI demandée et la méthode HTTP
     */
    public function matches($requestUri, $requestMethod)
    {
        // Si la méthode ne correspond pas, c'est terminé
        if ($this->method !== $requestMethod) {
            return false;
        }

        // Compare l'URI : exact match
        // (Dans une version plus avancée, tu pourrais gérer un match regex ou paramétrique)
        return ($this->uri === $requestUri);
    }

    /**
     * Exécute l'action (contrôleur@methode) de la route
     */
    public function execute($requestUri = '')
    {
        // On sépare "HomeController@index" -> ["HomeController", "index"]
        list($controller, $method) = explode('@', $this->action);

        // Construct le FQCN (namespace + nom du controller)
        $controllerFQCN = "App\\Controllers\\$controller";

        // Vérifie que la classe existe
        if (class_exists($controllerFQCN)) {
            $controllerObject = new $controllerFQCN();
            // Vérifie que la méthode existe dans ce controller
            if (method_exists($controllerObject, $method)) {
                // Exécution
                return $controllerObject->$method();
            }
        }

        // Si le contrôleur ou la méthode est introuvable -> 500
        http_response_code(500);
        echo "500 Internal Server Error";
    }

    /**
     * (Optionnel) Convertit une URI avec paramètres en regex
     *  ex: /games/{slug} => /games/(?P<slug>[^/]+)
     *
     * private function convertUriToRegex($uri)
     * {
     *     return '#^' . preg_replace('/\{([\w]+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
     * }
     */
}
