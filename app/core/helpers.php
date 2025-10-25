<?php
/**
 * Fichier de fonctions helper pour les vues
 * Ces fonctions simplifient l'écriture des templates
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

/**
 * Vérifie si une valeur existe et n'est pas vide
 * 
 * @param mixed $value La valeur à vérifier
 * @return bool
 */
function has($value)
{
    return isset($value) && !empty($value);
}

/**
 * Retourne une valeur par défaut si la valeur est vide
 * 
 * @param mixed $value La valeur à vérifier
 * @param mixed $default La valeur par défaut
 * @return mixed
 */
function default_value($value, $default = '')
{
    return !empty($value) ? $value : $default;
}
