<?php

/**
 * Contrôleur de gestion des livres
 * 
 * Gère le CRUD complet des livres (Create, Read, Update, Delete).
 * Utilise :
 * - ImageUploader pour la gestion des images
 * - Méthodes internes pour la validation (validate, sanitize)
 * - Méthodes internes pour vérifier les droits (findBookOrFail, findOwnBookOrFail)
 * 
 */

class BookController extends Controller
{
    private BookManager $bookManager;
    private UserManager $userManager;
    private ImageUploader $imageUploader;

    public function __construct()
    {
        $this->bookManager = $this->loadManager('Book');
        $this->userManager = $this->loadManager('User');
        $this->imageUploader = new ImageUploader();
    }

    /* ================================
       ACTIONS PUBLIQUES - CONSULTATION
       ================================ */

    /**
     * Affiche tous les livres disponibles (page publique)
     * Route : nos-livres
     */

    public function index(): void
    {
        // Récupérer le terme de recherche s'il existe
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Exclure les livres de l'utilisateur connecté s'il est connecté
        $excludeUserId = Session::isLoggedIn() ? Session::getUserId() : null;

        // Si recherche, utiliser searchBooks, sinon tous les livres
        $books = !empty($searchTerm)
            ? $this->bookManager->searchBooks($searchTerm, $excludeUserId)
            : $this->bookManager->findAvailableBooks($excludeUserId);

        $this->render('book/list', [
            'books' => $books,
            'searchTerm' => $searchTerm,
            'title' => 'Tous les livres - Tom Troc'
        ]);
    }

    /**
     * Affiche le détail d'un livre
     * Route : livre/{id}
     */

    public function show(int $id): void
    {
        $book = $this->findBookOrFail($id, 'nos-livres');
        if (!$book) return;

        // Récupérer le propriétaire
        $owner = $this->userManager->findById($book->getUserId());
        if (!$owner) {
            Session::setFlash('error', 'Propriétaire du livre introuvable.');
            $this->redirect('nos-livres');
            return;
        }

        $this->render('book/show', [
            'book' => $book,
            'owner' => $owner,
            'title' => $book->getTitle() . ' - Détail du livre'
        ]);
    }

    /* ================================
       ACTIONS CRUD - CRÉATION
       ================================ */

    /**
     * Traite l'ajout d'un livre (POST)
     * Route : book/create
     */

    public function create(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $this->validateCsrf('mon-compte');

        // Récupérer et valider les données
        $data = [
            'title' => $this->getPost('title', ''),
            'author' => $this->getPost('author', ''),
            'description' => $this->getPost('description', ''),
            'is_available' => $this->getPost('is_available', '0')
        ];

        $errors = $this->validate($data);

        // Gestion de l'upload d'image
        $imageName = $this->handleImageUpload($_FILES['image'] ?? null, $errors);

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->redirect('mon-compte#add-book-modal');
            return;
        }

        // Créer le livre
        $cleanData = $this->sanitize($data);
        $book = $this->bookManager->createBook(array_merge($cleanData, [
            'user_id' => Session::getUserId(),
            'image' => $imageName
        ]));

        $message = $book ? 'Livre ajouté avec succès !' : 'Erreur lors de l\'ajout du livre.';
        $type = $book ? 'success' : 'error';

        Session::setFlash($type, $message);
        $this->redirect($book ? 'mon-compte' : 'mon-compte#add-book-modal');
    }

    /* ================================
       ACTIONS CRUD - MODIFICATION
       ================================ */

    /**
     * Affiche le formulaire d'édition d'un livre (GET)
     * Route : book/{id}/edit
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $book = $this->findOwnBookOrFail($id);
        if (!$book) return;

        $this->render('book/edit', [
            'book' => $book,
            'title' => 'Modifier ' . escape($book->getTitle()),
            'csrfToken' => Session::generateCsrfToken(),
        ]);
    }

    /**
     * Traite la modification d'un livre (POST)
     * Route : book/{id}/update
     */
    public function update(int $id): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        $book = $this->findOwnBookOrFail($id);
        if (!$book) return;

        $this->validateCsrf('book/' . $id . '/edit');

        // Récupérer et valider les données
        $data = [
            'title' => $this->getPost('title', ''),
            'author' => $this->getPost('author', ''),
            'description' => $this->getPost('description', ''),
            'is_available' => $this->getPost('available', '0')
        ];

        $errors = $this->validate($data);

        // Gestion de l'upload d'image (conserver l'ancienne par défaut)
        $imageName = $this->handleImageUpdate($_FILES['image'] ?? null, $book, $errors);

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        // Mettre à jour le livre
        $cleanData = $this->sanitize($data);
        $updated = $this->bookManager->updateBook($id, array_merge($cleanData, [
            'image' => $imageName
        ]));

        $message = $updated ? 'Livre modifié avec succès !' : 'Erreur lors de la modification du livre.';
        $type = $updated ? 'success' : 'error';

        Session::setFlash($type, $message);
        $this->redirect($updated ? 'mon-compte' : 'book/' . $id . '/edit');
    }

    /**
     * Change le statut de disponibilité d'un livre (POST)
     * Route : book/{id}/toggle-availability
     */
    public function toggleAvailability(int $id): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $book = $this->findOwnBookOrFail($id);
        if (!$book) return;

        $this->validateCsrf('mon-compte');

        $newAvailability = !$book->getIsAvailable();
        $success = $this->bookManager->updateAvailability($id, $newAvailability);

        if ($success) {
            $statusText = $newAvailability ? 'disponible' : 'non disponible';
            Session::setFlash('success', 'Le livre est maintenant ' . $statusText . '.');
        } else {
            Session::setFlash('error', 'Erreur lors de la mise à jour de la disponibilité.');
        }

        $this->redirect('mon-compte');
    }

    /* ================================
       ACTIONS CRUD - SUPPRESSION
       ================================ */

    /**
     * Supprime un livre (POST)
     * Route : book/{id}/delete
     */

    public function delete(int $id): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $book = $this->findOwnBookOrFail($id);
        if (!$book) return;

        $this->validateCsrf('mon-compte');

        $result = $this->bookManager->deleteBook($id);

        if ($result['success']) {
            $this->imageUploader->delete($book->getImage(), 'book');
            Session::setFlash('success', 'Livre supprimé avec succès !');
        } else {
            Session::setFlash('error', $result['error']);
        }

        $this->redirect('mon-compte');
    }

    /**
     * Supprime uniquement l'image d'un livre (POST)
     * Route : book/delete-image
     */

    public function deleteImage(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $id = (int) $this->getPost('book_id', 0);
        if ($id === 0) {
            Session::setFlash('error', 'ID du livre invalide.');
            $this->redirect('mon-compte');
            return;
        }

        $book = $this->findOwnBookOrFail($id, 'book/' . $id . '/edit');
        if (!$book) return;

        // Valider CSRF
        $this->validateCsrf('book/' . $id . '/edit');

        // Supprimer l'image et mettre à jour avec le placeholder
        $this->imageUploader->delete($book->getImage(), 'book');

        $updated = $this->bookManager->updateBook($id, [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'image' => $this->imageUploader->getPlaceholder('book'),
            'description' => $book->getDescription(),
            'is_available' => $book->getIsAvailable() ? 1 : 0
        ]);

        $message = $updated ? 'Image supprimée avec succès !' : 'Erreur lors de la suppression de l\'image.';
        $type = $updated ? 'success' : 'error';

        Session::setFlash($type, $message);
        $this->redirect('book/' . $id . '/edit');
    }

    /* ================================
       MÉTHODES INTERNES - VALIDATION
       ================================ */

    /**
     * Valide les données d'un livre
     * 
     * @param array $data Données à valider ['title' => '', 'author' => '', 'description' => '']
     * @return array Tableau d'erreurs (vide si tout est valide)
     */

    private function validate(array $data): array
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

    private function sanitize(array $data): array
    {
        return [
            'title' => trim($data['title'] ?? ''),
            'author' => trim($data['author'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'is_available' => isset($data['is_available']) && $data['is_available'] === '1' ? 1 : 0
        ];
    }

    /* ================================
       MÉTHODES INTERNES - GESTION IMAGES
       ================================ */

    /**
     * Gère l'upload d'une nouvelle image lors de la création
     * 
     * @param array|null $file Fichier uploadé
     * @param array &$errors Tableau d'erreurs (passé par référence)
     * @return string Nom du fichier (nouveau ou placeholder)
     */

    private function handleImageUpload(?array $file, array &$errors): string
    {
        $imageName = $this->imageUploader->getPlaceholder('book');

        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->imageUploader->upload($file, 'book');

            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        }

        return $imageName;
    }

    /**
     * Gère la mise à jour d'une image existante lors de la modification
     * 
     * @param array|null $file Fichier uploadé
     * @param Book $book Livre existant
     * @param array &$errors Tableau d'erreurs (passé par référence)
     * @return string Nom du fichier (nouveau ou ancien)
     */

    private function handleImageUpdate(?array $file, Book $book, array &$errors): string
    {
        $imageName = $book->getImage();

        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->imageUploader->upload($file, 'book');

            if ($uploadResult['success']) {
                $this->imageUploader->delete($book->getImage(), 'book');
                $imageName = $uploadResult['filename'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        }

        return $imageName;
    }

    /* ================================
       MÉTHODES INTERNES - SÉCURITÉ & DROITS
       ================================ */

    /**
     * Récupère un livre et vérifie qu'il existe
     * 
     * @param int $id ID du livre
     * @param string $redirectUrl URL de redirection en cas d'erreur
     * @return Book|null Retourne le livre ou null si erreur (avec redirection)
     */
    protected function findBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
    {
        $book = $this->bookManager->findById($id);

        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect($redirectUrl);
            return null;
        }

        return $book;
    }

    /**
     * Récupère un livre et vérifie que l'utilisateur en est le propriétaire
     * 
     * Vérifie à la fois l'existence du livre et les droits de propriété.
     * 
     * @param int $id ID du livre
     * @param string $redirectUrl URL de redirection en cas d'erreur
     * @return Book|null Retourne le livre ou null si erreur (avec redirection)
     */
    protected function findOwnBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
    {
        $book = $this->findBookOrFail($id, $redirectUrl);

        if (!$book) {
            return null;
        }

        // Vérifier que l'utilisateur connecté est le propriétaire
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect($redirectUrl);
            return null;
        }

        return $book;
    }
}
