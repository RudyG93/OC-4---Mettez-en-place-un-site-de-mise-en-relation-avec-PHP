<?php
/**
 * Classe Controller - Contrôleur de base
 * 
 * Tous les contrôleurs de l'application héritent de cette classe.
 * Fournit des méthodes utilitaires pour charger les vues, rediriger, etc.
 */

abstract class Controller
{
    /**
     * Charge un manager (modèle)
     */
    protected function loadManager($manager)
    {
        $managerClass = $manager . 'Manager';
        $managerFile = APP_PATH . '/model/manager/' . $managerClass . '.php';
        
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

    /**
     * Récupère l'utilisateur connecté
     */
    protected function getCurrentUser()
    {
        if (!Session::isLoggedIn()) {
            return null;
        }
        
        $userManager = $this->loadManager('User');
        return $userManager->findById(Session::getUserId());
    }

    /**
     * Affiche une erreur et redirige
     */
    protected function error($message, $redirectTo = '')
    {
        Session::setFlash('error', $message);
        $this->redirect($redirectTo);
    }

    /**
     * Affiche un succès et redirige
     */
    protected function success($message, $redirectTo = '')
    {
        Session::setFlash('success', $message);
        $this->redirect($redirectTo);
    }

    /**
     * Vérifie qu'une ressource existe, sinon erreur
     */
    protected function ensureExists($resource, $errorMessage = 'Ressource introuvable', $redirectTo = '')
    {
        if (!$resource) {
            $this->error($errorMessage, $redirectTo);
        }
        return $resource;
    }

    /**
     * Valide le token CSRF
     */
    protected function validateCsrf($redirectTo = '')
    {
        if (!Session::verifyCsrfToken($this->getPost('csrf_token'))) {
            $this->error('Token de sécurité invalide. Veuillez réessayer.', $redirectTo);
        }
        return true;
    }

    /**
     * Génère et retourne un token CSRF pour les vues
     */
    protected function getCsrfToken()
    {
        return Session::generateCsrfToken();
    }

    /**
     * Récupère l'état du formulaire (old input, errors) et nettoie la session
     */
    protected function getFormState()
    {
        $oldInput = Session::get('old_input', []);
        $errors = Session::get('errors', []);
        
        // Nettoyer la session
        Session::remove('old_input');
        Session::remove('errors');
        
        return [
            'oldInput' => $oldInput,
            'errors' => $errors
        ];
    }

    /**
     * Sauvegarde l'état du formulaire pour redirection
     */
    protected function saveFormState(array $oldInput, array $errors = [])
    {
        if (!empty($oldInput)) {
            Session::set('old_input', $oldInput);
        }
        if (!empty($errors)) {
            Session::set('errors', $errors);
        }
    }
}
