<?php
/**
 * Script de diagnostic pour vérifier la structure de la table users
 */

// Inclure la configuration
require_once __DIR__ . '/config/config.php';

try {
    // Charger les classes nécessaires
    require_once APP_PATH . '/core/Database.php';
    
    echo "=== DIAGNOSTIC DE LA TABLE USERS ===\n\n";
    
    // Connexion à la base de données
    $db = Database::getInstance();
    echo "✅ Connexion à la base de données réussie\n\n";
    
    // Vérifier la structure de la table users
    echo "Structure de la table users :\n";
    echo "----------------------------\n";
    
    $stmt = $db->query('DESCRIBE users');
    $columns = $stmt->fetchAll();
    
    $hasLoginColumn = false;
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        if ($column['Field'] === 'login') {
            $hasLoginColumn = true;
        }
    }
    
    echo "\n";
    
    if ($hasLoginColumn) {
        echo "⚠️  PROBLÈME DÉTECTÉ : La colonne 'login' existe encore dans la table users\n";
        echo "   Cette colonne doit être supprimée pour éviter l'erreur de propriété dynamique.\n\n";
        
        echo "Solution recommandée :\n";
        echo "ALTER TABLE users DROP COLUMN login;\n\n";
    } else {
        echo "✅ Aucune colonne 'login' trouvée dans la table users\n\n";
    }
    
    // Tester une requête utilisateur
    echo "Test d'une requête utilisateur :\n";
    echo "--------------------------------\n";
    
    $stmt = $db->query('SELECT * FROM users LIMIT 1');
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Colonnes retournées par SELECT * :\n";
        foreach (array_keys($user) as $key) {
            if (is_string($key)) {
                echo "- $key\n";
            }
        }
    } else {
        echo "Aucun utilisateur trouvé dans la table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}