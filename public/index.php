<?php

/* Point d'entrée principal de l'application */

// Charger la configuration
require_once __DIR__ . '/../config/app.php';

function escape($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Autoloader pour charger automatiquement les classes
spl_autoload_register(function ($className) {
    $paths = [
        APP_PATH . '/core/',
        APP_PATH . '/controller/',
        APP_PATH . '/model/manager/',
        APP_PATH . '/model/entity/',
        APP_PATH . '/service/',
        APP_PATH . '/traits/',
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
} catch (Exception $error) {
    // En cas d'erreur critique
    if (ENVIRONMENT === 'development') {
        die("Erreur : " . $error->getMessage());
    } else {
        die("Une erreur est survenue. Veuillez réessayer plus tard.");
    }
}
