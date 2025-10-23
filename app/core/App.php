<?php
/**
 * Classe App - Routeur principal de l'application
 * Gère le routing et l'exécution des contrôleurs
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
        $url = $this->parseUrl();
        
        // Déterminer le contrôleur et l'action
        $this->resolveRoute($url);
        
        // Charger le contrôleur
        $this->loadController();
        
        // Exécuter l'action
        $this->executeAction();
    }
    
    /**
     * Parse l'URL et retourne un tableau des segments
     */
    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
    
    /**
     * Résout la route (contrôleur, action, paramètres)
     */
    private function resolveRoute($url)
    {
        // Reconstruire l'URL pour comparer avec les routes
        $urlString = implode('/', $url);
        
        // Vérifier si c'est une route personnalisée
        if (isset($this->routes[$urlString])) {
            $route = $this->routes[$urlString];
            $this->controller = $route['controller'];
            $this->action = $route['action'];
            return;
        }
        
        // Route dynamique standard : controller/action/params
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
    
    /**
     * Charge le contrôleur
     */
    private function loadController()
    {
        $controllerName = $this->controller . 'Controller';
        $controllerFile = APP_PATH . '/controller/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $this->controller = new $controllerName();
        } else {
            // Contrôleur non trouvé -> 404
            $this->show404();
        }
    }
    
    /**
     * Exécute l'action du contrôleur
     */
    private function executeAction()
    {
        if (method_exists($this->controller, $this->action)) {
            call_user_func_array([$this->controller, $this->action], $this->params);
        } else {
            // Action non trouvée -> 404
            $this->show404();
        }
    }
    
    /**
     * Affiche la page 404
     */
    private function show404()
    {
        http_response_code(404);
        require_once APP_PATH . '/controller/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->notFound();
        exit;
    }
}
