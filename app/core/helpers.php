<?php
/**
 * Fichier de fonctions utilitaires globales
 */

/* ================================
   SÉCURITÉ
   ================================ */

/**
 * Échappe une chaîne pour l'affichage HTML (protection XSS)
 * 
 * @param string|null $string Chaîne à échapper
 * @return string Chaîne échappée
 */
function escape($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
