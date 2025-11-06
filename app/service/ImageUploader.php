<?php

/**
 * Service de gestion des uploads d'images
 * 
 * Gère l'upload, la validation et la suppression d'images
 * pour les livres et les avatars utilisateurs.
 */
class ImageUploader
{
    /**
     * Configuration par type d'image
     */
    private const CONFIG = [
        'book' => [
            'upload_dir' => 'uploads/books/',
            'allowed_types' => ['image/jpeg', 'image/png'],
            'max_size' => 5242880, // 5 MB
            'prefix' => 'book_',
            'placeholder' => 'book_placeholder.png'
        ],
        'avatar' => [
            'upload_dir' => 'uploads/avatars/',
            'allowed_types' => ['image/jpeg', 'image/png'],
            'max_size' => 2097152, // 2 MB
            'prefix' => 'avatar_',
            'placeholder' => 'pp_placeholder.png'
        ]
    ];

    /**
     * Upload une image
     * 
     * @param array $file Fichier uploadé ($_FILES['fieldname'])
     * @param string $type Type d'image ('book' ou 'avatar')
     * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
     */
    public function upload(array $file, string $type = 'book'): array
    {
        // Vérifier que le type est valide
        if (!isset(self::CONFIG[$type])) {
            return [
                'success' => false,
                'filename' => null,
                'error' => 'Type d\'image invalide.'
            ];
        }

        $config = self::CONFIG[$type];

        // Vérifier qu'un fichier a été uploadé
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'filename' => null,
                'error' => 'Aucun fichier uploadé ou erreur lors de l\'upload.'
            ];
        }

        // Valider le type MIME
        if (!in_array($file['type'], $config['allowed_types'])) {
            return [
                'success' => false,
                'filename' => null,
                'error' => 'Format de fichier non autorisé. Utilisez JPG ou PNG.'
            ];
        }

        // Valider la taille
        if ($file['size'] > $config['max_size']) {
            $maxSizeMB = $config['max_size'] / 1048576;
            return [
                'success' => false,
                'filename' => null,
                'error' => "Le fichier est trop volumineux (max {$maxSizeMB} Mo)."
            ];
        }

        // Créer le dossier si nécessaire
        if (!is_dir($config['upload_dir'])) {
            mkdir($config['upload_dir'], 0755, true);
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $config['prefix'] . uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $config['upload_dir'] . $filename;

        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'filename' => null,
            'error' => 'Erreur lors de l\'upload du fichier.'
        ];
    }

    /**
     * Supprime une image
     * 
     * @param string $filename Nom du fichier à supprimer
     * @param string $type Type d'image ('book' ou 'avatar')
     * @return bool True si supprimé avec succès
     */
    public function delete(string $filename, string $type = 'book'): bool
    {
        // Ne pas supprimer les placeholders
        if ($this->isPlaceholder($filename, $type)) {
            return false;
        }

        // Vérifier que le type est valide
        if (!isset(self::CONFIG[$type])) {
            return false;
        }

        $config = self::CONFIG[$type];
        $filePath = $config['upload_dir'] . $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Vérifie si un fichier est un placeholder
     * 
     * @param string $filename Nom du fichier
     * @param string $type Type d'image ('book' ou 'avatar')
     * @return bool
     */
    public function isPlaceholder(string $filename, string $type = 'book'): bool
    {
        if (!isset(self::CONFIG[$type])) {
            return false;
        }

        return $filename === self::CONFIG[$type]['placeholder'];
    }

    /**
     * Récupère le placeholder pour un type d'image
     * 
     * @param string $type Type d'image ('book' ou 'avatar')
     * @return string
     */
    public function getPlaceholder(string $type = 'book'): string
    {
        return self::CONFIG[$type]['placeholder'] ?? 'placeholder.png';
    }
}
