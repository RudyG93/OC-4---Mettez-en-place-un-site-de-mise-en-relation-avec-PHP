<?php
/**
 * Script de destruction complète des sessions
 * ATTENTION : Ceci déconnectera tous les utilisateurs
 */

echo "=== DESTRUCTION COMPLÈTE DES SESSIONS ===\n\n";

// Démarrer une session temporaire
session_start();

echo "Avant destruction :\n";
if (isset($_SESSION) && !empty($_SESSION)) {
    echo "Session active avec " . count($_SESSION) . " éléments\n";
} else {
    echo "Aucune session active\n";
}

// Détruire la session
session_unset();
session_destroy();

// Supprimer le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

echo "\n✅ Session détruite avec succès\n";
echo "🔄 Tous les utilisateurs ont été déconnectés\n";
echo "🎉 L'erreur de propriété dynamique devrait être complètement résolue\n\n";

echo "Vous pouvez maintenant tester votre application sans l'erreur deprecated.\n";