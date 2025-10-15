<?php
/**
 * Point d'entrée principal de l'application
 */

// Charger la configuration
require_once __DIR__ . '/../config/config.php';

// Autoloader pour charger automatiquement les classes
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . '/core/',
        APP_PATH . '/controllers/',
        APP_PATH . '/models/managers/',
        APP_PATH . '/models/entities/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Démarrer l'application
try {
    $app = new App();
} catch (Exception $e) {
    // En cas d'erreur critique
    if (ENVIRONMENT === 'development') {
        die("Erreur : " . $e->getMessage());
    } else {
        die("Une erreur est survenue. Veuillez réessayer plus tard.");
    }
}
