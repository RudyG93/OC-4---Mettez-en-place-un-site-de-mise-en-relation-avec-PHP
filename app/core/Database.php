<?php

/**
 * Classe Database - Singleton pour la connexion PDO
 * 
 * Fournit une instance unique de connexion à la base de données.
 * Pattern Singleton pour éviter les connexions multiples.
 */
class Database
{
    private static $instance = null;
    private $pdo;

    /**
     * Constructeur privé - Initialise la connexion PDO
     * 
     * @throws PDOException En cas d'erreur de connexion
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $errorPDO) {
            error_log('[DATABASE ERROR] ' . $errorPDO->getMessage());
            exit('Une erreur est survenue. Merci de réessayer plus tard.');
        }
    }

    /**
     * Récupère l'instance unique de la connexion PDO
     * 
     * @return PDO Instance PDO
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    /**
     * Empêche le clonage de l'instance (pattern Singleton)
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation de l'instance (pattern Singleton)
     * 
     * @throws Exception Toujours
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
