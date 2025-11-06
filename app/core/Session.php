<?php

/**
 * Classe Session - Gestion des sessions utilisateur
 * 
 * Fournit des méthodes statiques pour gérer :
 * - Les données de session (get, set, remove)
 * - Les messages flash (notifications temporaires)
 * - L'authentification utilisateur
 * - Les tokens CSRF
 */

class Session
{
    /* ================================
       GESTION DE BASE - SESSION
       ================================ */

    /**
     * Démarre la session si elle n'est pas déjà démarrée
     * 
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Définit une valeur dans la session
     * 
     * @param string $key Clé de la donnée
     * @param mixed $value Valeur à stocker
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de la session
     * 
     * @param string $key Clé de la donnée
     * @param mixed $default Valeur par défaut si clé absente
     * @return mixed Valeur ou valeur par défaut
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe dans la session
     * 
     * @param string $key Clé à vérifier
     * @return bool True si la clé existe
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une valeur de la session
     * 
     * @param string $key Clé à supprimer
     * @return void
     */
    public static function remove(string $key): void
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Détruit complètement la session
     * 
     * @return void
     */
    public static function destroy(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Régénère l'ID de session (sécurité contre session fixation)
     * 
     * @return void
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /* ================================
       MESSAGES FLASH
       ================================ */

    /**
     * Définit un message flash (notification temporaire)
     * 
     * @param string $type Type de message ('success', 'error', 'warning', etc.)
     * @param string $message Contenu du message
     * @return void
     */
    public static function setFlash(string $type, string $message): void
    {
        self::set('flash', ['type' => $type, 'message' => $message]);
    }

    /**
     * Récupère et supprime le message flash
     * 
     * @return array|null Message flash ['type' => '', 'message' => ''] ou null
     */
    public static function getFlash(): ?array
    {
        $flash = self::get('flash');
        self::remove('flash');
        return $flash;
    }

    /* ================================
       AUTHENTIFICATION
       ================================ */

    /**
     * Vérifie si un utilisateur est connecté
     * 
     * @return bool True si connecté
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    /**
     * Récupère l'ID de l'utilisateur connecté
     * 
     * @return int|null ID de l'utilisateur ou null
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Connecte un utilisateur (crée la session)
     * 
     * @param int $userId ID de l'utilisateur
     * @return void
     */
    public static function login(int $userId): void
    {
        self::set('user_id', $userId);
        self::regenerate();
    }

    /**
     * Déconnecte l'utilisateur (détruit la session)
     * 
     * @return void
     */
    public static function logout(): void
    {
        self::destroy();
    }

    /* ================================
       PROTECTION CSRF
       ================================ */

    /**
     * Génère un token CSRF (réutilise l'existant si présent)
     * 
     * @return string Token CSRF
     */
    public static function generateCsrfToken(): string
    {
        // Réutiliser le token existant s'il y en a un
        $existingToken = self::get('csrf_token', '');
        if (!empty($existingToken)) {
            return $existingToken;
        }

        // Générer un nouveau token seulement s'il n'y en a pas
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }

    /**
     * Vérifie la validité d'un token CSRF
     * 
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return hash_equals(self::get('csrf_token', ''), $token);
    }

    /* ================================
       MAINTENANCE
       ================================ */

    /**
     * Nettoie la session des objets obsolètes
     * Conserve uniquement les données essentielles (user_id, username, csrf_token, flash)
     * 
     * @return void
     */
    public static function cleanSession(): void
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
