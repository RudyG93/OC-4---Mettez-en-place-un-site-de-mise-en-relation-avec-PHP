<?php
/**
 * Script de nettoyage des sessions obsolètes
 * À exécuter une fois pour nettoyer les sessions contenant des objets User obsolètes
 */

// Inclure la configuration
require_once __DIR__ . '/config/config.php';

// Charger les classes nécessaires
require_once APP_PATH . '/core/Session.php';

echo "=== NETTOYAGE DES SESSIONS OBSOLÈTES ===\n\n";

try {
    // Démarrer la session
    Session::start();
    
    echo "Session actuelle avant nettoyage :\n";
    if (isset($_SESSION) && !empty($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            echo "- $key: $type\n";
        }
    } else {
        echo "- Session vide\n";
    }
    
    echo "\nNettoyage en cours...\n";
    
    // Nettoyer la session
    Session::cleanSession();
    
    echo "✅ Session nettoyée avec succès\n\n";
    
    echo "Session après nettoyage :\n";
    if (isset($_SESSION) && !empty($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            echo "- $key: $type\n";
        }
    } else {
        echo "- Session vide\n";
    }
    
    echo "\n🎉 Nettoyage terminé ! L'erreur de propriété dynamique devrait être résolue.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur durant le nettoyage : " . $e->getMessage() . "\n";
}

// Optionnel : détruire complètement la session pour un nettoyage total
echo "\nVoulez-vous détruire complètement la session ? (O/N)\n";
echo "Cela déconnectera tous les utilisateurs mais garantit un nettoyage total.\n";
// En production, vous pourriez vouloir décommenter la ligne suivante :
// Session::destroy();
// echo "Session détruite complètement.\n";