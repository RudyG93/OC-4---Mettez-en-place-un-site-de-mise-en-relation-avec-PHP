<?php

/**
 * Classe Controller - Contrôleur de base
 * 
 * Tous les contrôleurs de l'application héritent de cette classe.
 * Fournit des méthodes utilitaires pour :
 * - Charger les managers (modèles)
 * - Rendre les vues avec layout
 * - Gérer les redirections
 * - Valider les requêtes POST
 * - Vérifier l'authentification
 * - Valider les tokens CSRF
 */
abstract class Controller
{
    /**
     * Charge un manager (modèle)
     * 
     * @param string $manager Nom du manager (ex: 'Book' pour BookManager)
     * @return object Instance du manager
     * @throws Exception Si le manager n'existe pas
     */
    protected function loadManager($manager): object
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
     * Charge et affiche une vue avec son layout
     * 
     * Ajoute automatiquement le compteur de messages non lus pour les utilisateurs connectés.
     * 
     * @param string $view Chemin de la vue (ex: 'book/list')
     * @param array $data Données à passer à la vue
     * @param string $layout Nom du layout (défaut: 'main')
     * @throws Exception Si la vue n'existe pas
     */
    protected function render($view, $data = [], $layout = 'main'): void
    {
        // Ajouter le compteur de messages non lus si l'utilisateur est connecté
        if (Session::isLoggedIn() && !isset($data['unreadMessagesCount'])) {
            $messageManager = $this->loadManager('Message');
            $data['unreadMessagesCount'] = $messageManager->getUnreadCount(Session::getUserId());
        }

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
     * Redirige vers une URL relative
     * 
     * @param string $path Chemin relatif (ex: 'mon-compte', 'book/123/edit')
     */
    protected function redirect($path)
    {
        $url = BASE_URL . ltrim($path, '/');
        header("Location: $url");
        exit;
    }

    /**
     * Vérifie si l'utilisateur est connecté, sinon redirige vers login
     * 
     * @return void Redirige si non connecté
     */
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            $this->redirect('login');
        }
    }

    /**
     * Vérifie si la requête est de type POST
     * 
     * @return bool True si POST, false sinon
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }


    /**
     * Récupère une valeur POST nettoyée (trim)
     * 
     * @param string|null $key Clé POST ou null pour tout $_POST
     * @param mixed $default Valeur par défaut si clé absente
     * @return mixed Valeur POST nettoyée ou tableau complet
     */
    protected function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Valide le token CSRF et redirige en cas d'échec
     * 
     * @param string $redirectTo URL de redirection en cas d'erreur
     * @return bool True si valide
     */
    protected function validateCsrf($redirectTo = '')
    {
        if (!Session::verifyCsrfToken($this->getPost('csrf_token'))) {
            Session::setFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect($redirectTo);
        }
        return true;
    }
}
