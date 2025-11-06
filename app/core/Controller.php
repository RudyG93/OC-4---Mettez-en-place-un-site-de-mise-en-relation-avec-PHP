<?php
/*
 * Classe Controller - Contrôleur de base
 * 
 * Tous les contrôleurs de l'application héritent de cette classe.
 * Fournit des méthodes utilitaires pour charger les vues, rediriger, etc.
 * 
 * loadManager($manager) - Charge un manager (modèle)
 * render($view, $data = [], $layout = 'main') - Charge une vue avec un layout
 * 
 */

abstract class Controller
{
    /* Charge un manager (modèle) */

    protected function loadManager($manager) : object
    {
        $managerClass = $manager . 'Manager';
        $managerFile = APP_PATH . '/model/manager/' . $managerClass . '.php';
        
        if (file_exists($managerFile)) {
            require_once $managerFile;
            return new $managerClass();
        }
        
        throw new Exception("Manager $managerClass introuvable");
    }
    
    /* Charge une vue */

    protected function render($view, $data = [], $layout = 'main') : void
    {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);
        
        // Capturer le contenu de la vue
        ob_start();
        $viewFile = APP_PATH . '/view/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("Vue $view introuvable");
        }
        
        $content = ob_get_clean();
        
        // Charger le layout
        $layoutFile = APP_PATH . '/view/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /* Redirige vers une URL */

    protected function redirect($path)
    {
        $url = BASE_URL . ltrim($path, '/');
        header("Location: $url");
        exit;
    }

    /* Vérifie si la requête est POST */

    protected function isPost() : bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /* Vérifie si l'utilisateur est connecté */

    protected function requireAuth() : void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            $this->redirect('login');
        }
    }
    
    /* Récupère les données POST nettoyées */
    
    protected function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /* Valide le token CSRF  */

    protected function validateCsrf($redirectTo = '')
    {
        if (!Session::verifyCsrfToken($this->getPost('csrf_token'))) {
            Session::setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect($redirectTo);
        }
        return true;
    }

    /* Génère et retourne un token CSRF pour les vues */

    protected function getCsrfToken()
    {
        return Session::generateCsrfToken();
    }
}