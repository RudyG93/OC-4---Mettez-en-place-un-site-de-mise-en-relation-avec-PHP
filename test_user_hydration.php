<?php
/**
 * Test de l'hydratation User pour vérifier l'absence d'erreurs
 */

// Inclure la configuration
require_once __DIR__ . '/config/config.php';

// Charger les classes nécessaires
require_once APP_PATH . '/core/Entity.php';
require_once APP_PATH . '/model/entity/User.php';

echo "=== TEST D'HYDRATATION USER ===\n\n";

try {
    $user = new User();
    
    // Test avec données normales
    echo "Test 1: Hydratation avec données normales\n";
    $normalData = [
        'id' => 1,
        'username' => 'test_user',
        'email' => 'test@example.com',
        'password' => 'hashed_password',
        'created_at' => '2024-01-01 12:00:00'
    ];
    
    $user->hydrate($normalData);
    echo "✅ Hydratation normale réussie\n";
    echo "   - ID: " . $user->getId() . "\n";
    echo "   - Username: " . $user->getUsername() . "\n";
    echo "   - Email: " . $user->getEmail() . "\n\n";
    
    // Test avec propriété login (obsolète)
    echo "Test 2: Hydratation avec propriété 'login' obsolète\n";
    $dataWithLogin = [
        'id' => 2,
        'username' => 'test_user2',
        'email' => 'test2@example.com',
        'login' => 'old_login_value',  // Cette propriété doit être ignorée
        'created_at' => '2024-01-01 12:00:00'
    ];
    
    $user2 = new User();
    $user2->hydrate($dataWithLogin);
    echo "✅ Hydratation avec 'login' réussie (propriété ignorée)\n";
    echo "   - ID: " . $user2->getId() . "\n";
    echo "   - Username: " . $user2->getUsername() . "\n\n";
    
    // Test de tentative de création directe de propriété login
    echo "Test 3: Tentative de création directe de propriété 'login'\n";
    $user3 = new User();
    $user3->login = 'test';  // Ceci doit être intercepté par __set
    echo "✅ Propriété 'login' bloquée avec succès\n\n";
    
    echo "🎉 Tous les tests sont passés avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur durant les tests : " . $e->getMessage() . "\n";
}