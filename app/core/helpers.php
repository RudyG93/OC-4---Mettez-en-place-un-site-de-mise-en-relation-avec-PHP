<?php
/**
 * Fichier de fonctions helper pour les vues
 * Ces fonctions simplifient l'écriture des templates
 * 
 * Optimisé : Suppression des fonctions inutilisées
 */

/**
 * Échappe les caractères HTML pour sécuriser l'affichage
 * Raccourci pour htmlspecialchars()
 * 
 * @param string $string La chaîne à échapper
 * @return string La chaîne échappée
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Alias de e() pour compatibilité
 */
function escape($string)
{
    return e($string);
}
