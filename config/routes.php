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
    
    // Profil utilisateur
    'mon-compte' => ['controller' => 'User', 'action' => 'account'],
    'profil' => ['controller' => 'User', 'action' => 'profile'],
    
    // Livres
    'nos-livres' => ['controller' => 'Book', 'action' => 'list'],
    'livre' => ['controller' => 'Book', 'action' => 'show'],
    
    // Messagerie
    'messagerie' => ['controller' => 'Message', 'action' => 'inbox'],
    'conversation' => ['controller' => 'Message', 'action' => 'conversation'],
];
