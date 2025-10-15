<?php
/**
 * Classe Controller - Contrôleur de base
 * Tous les contrôleurs héritent de cette classe
 */

abstract class Controller
{
    /**
     * Charge un manager (modèle)
     */
    protected function loadManager($manager)
    {
        $managerClass = $manager . 'Manager';
        $managerFile = APP_PATH . '/models/managers/' . $managerClass . '.php';
        
        if (file_exists($managerFile)) {
            require_once $managerFile;
            return new $managerClass();
        }
        
        throw new Exception("Manager $managerClass introuvable");
    }
    
    /**
     * Charge une vue
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);
        
        // Capturer le contenu de la vue
        ob_start();
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("Vue $view introuvable");
        }
        
        $content = ob_get_clean();
        
        // Charger le layout
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * Redirige vers une URL
     */
    protected function redirect($path)
    {
        $url = BASE_URL . ltrim($path, '/');
        header("Location: $url");
        exit;
    }
    
    /**
     * Retourne une réponse JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Vérifie si la requête est POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Vérifie si la requête est GET
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    protected function requireAuth()
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            $this->redirect('login');
        }
    }
    
    /**
     * Récupère les données POST nettoyées
     */
    protected function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }
    
    /**
     * Récupère les données GET nettoyées
     */
    protected function getQuery($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }
    
    /**
     * Nettoie une chaîne pour l'affichage HTML
     */
    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
