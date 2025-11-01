<?php

/**
 * Service de validation des données de livres
 * 
 * Centralise toutes les règles de validation pour les livres.
 */
class BookValidator
{
    /**
     * Valide les données d'un livre
     * 
     * @param array $data Données à valider ['title' => '', 'author' => '', 'description' => '']
     * @return array Tableau d'erreurs (vide si tout est valide)
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validation du titre
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $errors['title'] = 'Le titre est obligatoire.';
        } elseif (strlen($title) > 255) {
            $errors['title'] = 'Le titre ne doit pas dépasser 255 caractères.';
        }

        // Validation de l'auteur
        $author = trim($data['author'] ?? '');
        if (empty($author)) {
            $errors['author'] = 'L\'auteur est obligatoire.';
        } elseif (strlen($author) > 255) {
            $errors['author'] = 'L\'auteur ne doit pas dépasser 255 caractères.';
        }

        // Validation de la description (optionnelle)
        $description = trim($data['description'] ?? '');
        if (!empty($description) && strlen($description) > 1000) {
            $errors['description'] = 'La description ne doit pas dépasser 1000 caractères.';
        }

        return $errors;
    }

    /**
     * Nettoie et prépare les données d'un livre pour l'enregistrement
     * 
     * @param array $data Données brutes
     * @return array Données nettoyées
     */
    public function sanitize(array $data): array
    {
        return [
            'title' => trim($data['title'] ?? ''),
            'author' => trim($data['author'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'is_available' => isset($data['is_available']) && $data['is_available'] === '1' ? 1 : 0
        ];
    }
}
