<?php
/**
 * Configuration des routes personnalisées
 * Format: 'url' => ['controller' => 'ControllerName', 'action' => 'actionName']
 */

return [
    // Page d'accueil
    '' => ['controller' => 'Home', 'action' => 'index'],
    'home' => ['controller' => 'Home', 'action' => 'index'],
    
    // Authentification
    'login' => ['controller' => 'Auth', 'action' => 'login'],
    'register' => ['controller' => 'Auth', 'action' => 'register'],
    'logout' => ['controller' => 'Auth', 'action' => 'logout'],
    
    // Mon compte
    'mon-compte' => ['controller' => 'Profile', 'action' => 'view'],
    'mon-compte/modifier' => ['controller' => 'Profile', 'action' => 'edit'],
    'mon-compte/update' => ['controller' => 'Profile', 'action' => 'update'],
    'profil' => ['controller' => 'Profile', 'action' => 'show'],
    
    // Livres
    'nos-livres' => ['controller' => 'Book', 'action' => 'list'],
    'livre' => ['controller' => 'Book', 'action' => 'show'],
    
    // Messagerie
    'messagerie' => ['controller' => 'Message', 'action' => 'inbox'],
    'conversation' => ['controller' => 'Message', 'action' => 'conversation'],
];
