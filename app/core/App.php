<?php

/**
 * Classe App - Routeur principal de l'application
 * 
 * Gère le routing et l'exécution des contrôleurs.
 * C'est le point d'entrée de toutes les requêtes HTTP.
 */

class App
{
    private $controller;
    private $action;
    private $params = [];
    private $routes;

    public function __construct()
    {
        // Charger les routes personnalisées
        $this->routes = require CONFIG_PATH . '/routes.php';

        // Démarrer la session
        Session::start();

        // Parser l'URL
        $url = [];
        if (isset($_GET['url'])) {
            $urlTemp = rtrim($_GET['url'], '/');
            $urlTemp = filter_var($urlTemp, FILTER_SANITIZE_URL);
            $url = explode('/', $urlTemp);
        }

        // Résoudre la route (contrôleur, action, paramètres)
        // Reconstruire l'URL pour comparer avec les routes
        $urlString = implode('/', $url);

        // Vérifier si c'est une route personnalisée exacte
        if (isset($this->routes[$urlString])) {
            $route = $this->routes[$urlString];
            $this->controller = $route['controller'];
            $this->action = $route['action'];
        } else {
            // Vérifier les routes avec paramètres dynamiques
            $routeFound = false;
            foreach ($this->routes as $pattern => $route) {
                if (strpos($pattern, '{') !== false) {
                    $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
                    $regex = '#^' . $regex . '$#';

                    if (preg_match($regex, $urlString, $matches)) {
                        $this->controller = $route['controller'];
                        $this->action = $route['action'];

                        // Extraire les paramètres
                        array_shift($matches); // Enlever le match complet
                        $this->params = $matches;
                        $routeFound = true;
                        break;
                    }
                }
            }

            // Route dynamique standard : controller/action/params
            if (!$routeFound) {
                if (isset($url[0]) && !empty($url[0])) {
                    $this->controller = ucfirst($url[0]);
                    unset($url[0]);
                } else {
                    $this->controller = 'Home';
                }

                if (isset($url[1]) && !empty($url[1])) {
                    $this->action = $url[1];
                    unset($url[1]);
                } else {
                    $this->action = 'index';
                }

                // Les paramètres restants
                $this->params = $url ? array_values($url) : [];
            }
        }

        // Charger le contrôleur
        $controllerName = $this->controller . 'Controller';
        $controllerFile = APP_PATH . '/controller/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $this->controller = new $controllerName();
        } else {
            // Contrôleur non trouvé -> 404
            http_response_code(404);
            require_once APP_PATH . '/controller/ErrorController.php';
            $errorController = new ErrorController();
            $errorController->notFound();
            exit;
        }

        // Exécuter l'action
        if (method_exists($this->controller, $this->action)) {
            call_user_func_array([$this->controller, $this->action], $this->params);
        } else {
            // Action non trouvée -> 404
            http_response_code(404);
            require_once APP_PATH . '/controller/ErrorController.php';
            $errorController = new ErrorController();
            $errorController->notFound();
            exit;
        }
    }
}
