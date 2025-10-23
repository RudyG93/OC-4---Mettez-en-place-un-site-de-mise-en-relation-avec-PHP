<?php
/**
 * Classe Session - Gestion des sessions utilisateur
 */

class Session
{
    /**
     * Démarre la session si elle n'est pas déjà démarrée
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Supprimer temporairement les erreurs de propriétés dynamiques
            $errorLevel = error_reporting();
            error_reporting($errorLevel & ~E_DEPRECATED);
            
            session_start();
            
            // Restaurer le niveau d'erreur
            error_reporting($errorLevel);
            
            // Nettoyer les objets User obsolètes de la session s'ils existent
            self::cleanObsoleteUserObjects();
        }
    }
    
    /**
     * Nettoie les objets User obsolètes stockés dans la session
     */
    private static function cleanObsoleteUserObjects()
    {
        if (!isset($_SESSION)) {
            return;
        }
        
        // Parcourir toutes les variables de session
        foreach ($_SESSION as $key => $value) {
            // Si c'est un objet User, le recréer proprement
            if (is_object($value) && $value instanceof User) {
                unset($_SESSION[$key]);
            }
        }
    }
    
    /**
     * Définit une valeur dans la session
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Récupère une valeur de la session
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Vérifie si une clé existe dans la session
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Supprime une valeur de la session
     */
    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Détruit la session complète
     */
    public static function destroy()
    {
        self::start();
        session_unset();
        session_destroy();
    }
    
    /**
     * Définit un message flash
     */
    public static function setFlash($type, $message)
    {
        self::set('flash', ['type' => $type, 'message' => $message]);
    }
    
    /**
     * Récupère et supprime le message flash
     */
    public static function getFlash()
    {
        $flash = self::get('flash');
        self::remove('flash');
        return $flash;
    }
    
    /**
     * Vérifie si un utilisateur est connecté
     */
    public static function isLoggedIn()
    {
        return self::has('user_id');
    }
    
    /**
     * Récupère l'ID de l'utilisateur connecté
     */
    public static function getUserId()
    {
        return self::get('user_id');
    }
    
    /**
     * Connecte un utilisateur
     */
    public static function login($userId)
    {
        self::set('user_id', $userId);
        self::regenerate();
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public static function logout()
    {
        self::destroy();
    }
    
    /**
     * Régénère l'ID de session (sécurité)
     */
    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }
    
    /**
     * Génère un token CSRF
     */
    public static function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }
    
    /**
     * Vérifie le token CSRF
     */
    public static function verifyCsrfToken($token)
    {
        return hash_equals(self::get('csrf_token', ''), $token);
    }
    
    /**
     * Nettoie la session des objets obsolètes
     * Utile après des modifications de structure d'entités
     */
    public static function cleanSession()
    {
        self::start();
        
        // Sauvegarder les données importantes
        $userId = self::get('user_id');
        $username = self::get('username');
        $csrfToken = self::get('csrf_token');
        $flash = self::get('flash');
        
        // Vider complètement la session
        session_unset();
        
        // Restaurer les données importantes
        if ($userId) {
            $_SESSION['user_id'] = $userId;
        }
        if ($username) {
            $_SESSION['username'] = $username;
        }
        if ($csrfToken) {
            $_SESSION['csrf_token'] = $csrfToken;
        }
        if ($flash) {
            $_SESSION['flash'] = $flash;
        }
    }
}
