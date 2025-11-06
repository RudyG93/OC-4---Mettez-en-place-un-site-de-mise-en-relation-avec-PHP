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

/* ================================
   FORMATAGE DE DATES
   ================================ */

/**
 * Formate une date pour l'affichage des messages
 * Format : "HH:mm" si aujourd'hui, "Hier HH:mm" si hier, "DD/MM/YYYY HH:mm" sinon
 * 
 * @param string $datetime Date/heure au format SQL (Y-m-d H:i:s)
 * @return string Date formatée
 */
function formatMessageDate($datetime)
{
    $timestamp = strtotime($datetime);
    $today = date('Y-m-d');
    $messageDate = date('Y-m-d', $timestamp);

    if ($messageDate === $today) {
        return date('H:i', $timestamp);
    } elseif ($messageDate === date('Y-m-d', strtotime('-1 day'))) {
        return 'Hier ' . date('H:i', $timestamp);
    } else {
        return date('d/m/Y H:i', $timestamp);
    }
}

/* ================================
   FORMATAGE DE TEXTE
   ================================ */

/**
 * Retourne un extrait tronqué d'un texte
 * 
 * @param string $text Texte complet
 * @param int $maxLength Longueur maximale de l'extrait
 * @return string Texte tronqué avec "..." si nécessaire
 */
function getTextExcerpt($text, $maxLength = 50)
{
    if (strlen($text) <= $maxLength) {
        return $text;
    }

    return substr($text, 0, $maxLength) . '...';
}

/* ================================
   HELPERS MESSAGES
   ================================ */

/**
 * Vérifie si un message a été envoyé par l'utilisateur donné
 * 
 * @param Message $message Objet message
 * @param int $userId ID de l'utilisateur
 * @return bool True si l'utilisateur est l'expéditeur
 */
function isMessageSentBy($message, $userId)
{
    return $message->getSenderId() == $userId;
}
