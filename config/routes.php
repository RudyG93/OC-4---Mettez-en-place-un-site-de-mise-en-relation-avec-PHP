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
    'mon-compte/update' => ['controller' => 'Profile', 'action' => 'update'],
    'mon-compte/update-avatar' => ['controller' => 'Profile', 'action' => 'updateAvatar'],
    'mon-compte/delete-avatar' => ['controller' => 'Profile', 'action' => 'deleteAvatar'],
    'profil/{id}' => ['controller' => 'Profile', 'action' => 'show'],
    
    // Livres
    'nos-livres' => ['controller' => 'Book', 'action' => 'index'],
    'livre/{id}' => ['controller' => 'Book', 'action' => 'show'],
    'livre/recherche' => ['controller' => 'Book', 'action' => 'search'],
    
    // Gestion des livres
    'book/create' => ['controller' => 'Book', 'action' => 'create'],
    'book/delete-image' => ['controller' => 'Book', 'action' => 'deleteImage'],
    'book/{id}/toggle-availability' => ['controller' => 'Book', 'action' => 'toggleAvailability'],
    'book/{id}/edit' => ['controller' => 'Book', 'action' => 'edit'],
    'book/{id}/update' => ['controller' => 'Book', 'action' => 'update'],
    'book/{id}/delete' => ['controller' => 'Book', 'action' => 'delete'],
    
    // Messagerie
    'messages' => ['controller' => 'Message', 'action' => 'index'],
    'messages/conversation/{id}' => ['controller' => 'Message', 'action' => 'conversation'],
    'messages/compose/{id}' => ['controller' => 'Message', 'action' => 'compose'],
    'messages/send' => ['controller' => 'Message', 'action' => 'send'],
    'messagerie' => ['controller' => 'Message', 'action' => 'index'],
    'conversation' => ['controller' => 'Message', 'action' => 'conversation'],
];
