<?php
/**
 * Fichier de fonctions helper pour les vues
 * Ces fonctions simplifient l'écriture des templates
 * 
 * Optimisé : Suppression des fonctions inutilisées
 */

/**
 * Échappe les caractères HTML pour sécuriser l'affichage
 * 
 * Protection contre les attaques XSS (Cross-Site Scripting).
 * Convertit les caractères spéciaux HTML en entités HTML.
 * 
 * Raccourci pour htmlspecialchars($string, ENT_QUOTES, 'UTF-8')
 * 
 * @param string|null $string La chaîne à échapper
 * @return string La chaîne échappée (ou vide si null)
 * 
 * @example
 * <?= e($book->getTitle()) ?>
 * <?= e($user->getBio()) ?>
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Alias de e() pour compatibilité et clarté
 * 
 * Certains développeurs préfèrent un nom plus explicite.
 * 
 * @param string|null $string La chaîne à échapper
 * @return string La chaîne échappée
 */
function escape($string)
{
    return e($string);
}