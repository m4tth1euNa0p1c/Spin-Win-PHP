<?php
namespace App\Core;

use Twig\Environment;
use PDO;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Route
{
    private $method;
    private $uri;
    private $action;
    private $name;
    private $paramsPattern;

    public function __construct($method, $uri, $action)
    {
        $this->method = strtoupper($method);
        $this->uri = rtrim($uri, '/');
        $this->action = $action;

    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function matches($requestUri, $requestMethod)
    {
        // Si la méthode ne correspond pas, c'est terminé
        if ($this->method !== strtoupper($requestMethod)) {
            return false;
        }

        return ($this->uri === $requestUri);
    }

    public function execute(Environment $twig, PDO $pdo, $requestUri = '')
    {
        try {
            list($controller, $method) = explode('@', $this->action);

            $controllerFQCN = "App\\Controllers\\$controller";

            if (class_exists($controllerFQCN)) {
                $controllerObject = new $controllerFQCN($twig, $pdo);
                if (method_exists($controllerObject, $method)) {
                    return $controllerObject->$method();
                } else {
                    throw new \Exception("Méthode '$method' non trouvée dans le contrôleur '$controllerFQCN'.");
                }
            } else {
                throw new \Exception("Contrôleur '$controllerFQCN' non trouvé.");
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());

            try {
                http_response_code(500);
                echo $twig->render('Errors/500.html.twig', ['message' => $e->getMessage()]);
            } catch (LoaderError | RuntimeError | SyntaxError $twigError) {
                echo "500 Internal Server Error";
            }
        }
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
