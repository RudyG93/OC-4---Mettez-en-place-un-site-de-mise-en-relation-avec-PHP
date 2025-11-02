<?php
/**
 * Configuration générale de l'application
 */

// Définir les constantes de chemins
define('ROOT', dirname(__DIR__));
define('APP_PATH', ROOT . '/app');
define('PUBLIC_PATH', ROOT . '/public');
define('CONFIG_PATH', ROOT . '/config');

// URL de base de l'application
define('BASE_URL', '/tests/Projet4/public/');

// Environnement (development ou production)
define('ENVIRONMENT', 'development');

// Configuration des erreurs selon l'environnement
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Charger la configuration locale (identifiants BDD)
if (file_exists(CONFIG_PATH . '/app.local.php')) {
    require_once CONFIG_PATH . '/app.local.php';
} else {
    die('Erreur : Le fichier app.local.php est manquant. Veuillez copier app.example.php vers app.local.php et le configurer.');
}
