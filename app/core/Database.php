<?php

/* Classe Database - Singleton pour la connexion PDO */

class Database
{
    private static $instance = null;
    private $pdo;

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

    /* Récupère l'instance unique de la connexion PDO */

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    /* Empêche le clonage de l'instance */

    private function __clone() {}

    /* Empêche la désérialisation de l'instance */
    
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
