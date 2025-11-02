<?php

/**
 * Contrôleur pour la gestion des livres
 * 
 * Refactorisé avec services (BookValidator, ImageUploader) 
 * et trait (ManagesBookOwnership) pour réduire la duplication
 */
class BookController extends Controller
{
    use ManagesBookOwnership;

    private BookManager $bookManager;
    private UserManager $userManager;
    private BookValidator $validator;
    private ImageUploader $imageUploader;

    public function __construct()
    {
        $this->bookManager = $this->loadManager('Book');
        $this->userManager = $this->loadManager('User');
        $this->validator = new BookValidator();
        $this->imageUploader = new ImageUploader();
    }

    /**
     * Affiche tous les livres disponibles (page publique)
     */
    public function index(): void
    {
        // Récupérer le terme de recherche s'il existe
        $searchTerm = $this->getQuery('q', '');
        
        // Exclure les livres de l'utilisateur connecté s'il est connecté
        $excludeUserId = Session::isLoggedIn() ? Session::getUserId() : null;

        // Si recherche, utiliser searchBooks, sinon tous les livres
        $books = !empty($searchTerm) 
            ? $this->bookManager->searchBooks($searchTerm, $excludeUserId)
            : $this->bookManager->findAvailableBooks($excludeUserId);
        
        $this->render('book/list', [
            'books' => $books,
            'searchTerm' => $searchTerm,
            'title' => 'Tous les livres disponibles'
        ]);
    }

    /**
     * Traite l'ajout d'un livre
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

        $errors = $this->validator->validate($data);
        
        // Gestion de l'upload d'image
        $imageName = $this->handleImageUpload($_FILES['image'] ?? null, $errors);

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState($data, $errors);
            $this->redirect('mon-compte#add-book-modal');
            return;
        }

        // Créer le livre
        $cleanData = $this->validator->sanitize($data);
        $book = $this->bookManager->createBook(array_merge($cleanData, [
            'user_id' => Session::getUserId(),
            'image' => $imageName
        ]));
        
        $message = $book ? 'Livre ajouté avec succès !' : 'Erreur lors de l\'ajout du livre.';
        $type = $book ? 'success' : 'error';
        
        Session::setFlash($type, $message);
        $this->redirect($book ? 'mon-compte' : 'mon-compte#add-book-modal');
    }

    /**
     * Affiche le détail d'un livre
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

        // Récupérer d'autres livres du propriétaire (max 3, excluant le livre actuel)
        $otherBooks = array_slice(
            array_filter(
                $this->bookManager->findByUserId($owner->getId(), 4),
                fn($book) => $book->getId() !== $id
            ),
            0,
            3
        );
        
        $this->render('book/show', [
            'book' => $book,
            'owner' => $owner,
            'otherBooks' => $otherBooks,
            'title' => $book->getTitle() . ' - Détail du livre'
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un livre
     */
    public function edit(int $id): void
    {
        $this->requireAuth();
        
        $book = $this->findOwnBookOrFail($id);
        if (!$book) return;

        $formState = $this->getFormState();

        $this->render('book/edit', [
            'book' => $book,
            'title' => 'Modifier ' . $this->escape($book->getTitle()),
            'csrfToken' => $this->getCsrfToken(),
            'oldInput' => $formState['oldInput'],
            'errors' => $formState['errors']
        ]);
    }

    /**
     * Traite la modification d'un livre
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

        $errors = $this->validator->validate($data);

        // Gestion de l'upload d'image (conserver l'ancienne par défaut)
        $imageName = $this->handleImageUpdate($_FILES['image'] ?? null, $book, $errors);

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState($data, $errors);
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        // Mettre à jour le livre
        $cleanData = $this->validator->sanitize($data);
        $updated = $this->bookManager->updateBook($id, array_merge($cleanData, [
            'image' => $imageName
        ]));
        
        $message = $updated ? 'Livre modifié avec succès !' : 'Erreur lors de la modification du livre.';
        $type = $updated ? 'success' : 'error';
        
        Session::setFlash($type, $message);
        $this->redirect($updated ? 'mon-compte' : 'book/' . $id . '/edit');
    }

    /**
     * Supprime un livre
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
     * Supprime l'image d'un livre
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
        if (!isset($_POST['csrf_token']) || !Session::verifyCsrfToken($_POST['csrf_token'])) {
            Session::setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        // Supprimer l'image et mettre à jour avec le placeholder
        $this->imageUploader->delete($book->getImage(), 'book');

        $updated = $this->bookManager->updateBook($id, [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'image' => $this->imageUploader->getPlaceholder('book'),
            'description' => $book->getDescription(),
            'is_available' => $book->isAvailable() ? 1 : 0
        ]);
        
        $message = $updated ? 'Image supprimée avec succès !' : 'Erreur lors de la suppression de l\'image.';
        $type = $updated ? 'success' : 'error';
        
        Session::setFlash($type, $message);
        $this->redirect('book/' . $id . '/edit');
    }

    /**
     * Recherche de livres
     */
    public function search(): void
    {
        $searchTerm = $this->getQuery('q', '');
        $excludeUserId = Session::isLoggedIn() ? Session::getUserId() : null;
        
        $books = !empty($searchTerm) 
            ? $this->bookManager->searchBooks($searchTerm, $excludeUserId)
            : [];
        
        $this->render('book/search', [
            'books' => $books,
            'searchTerm' => $searchTerm,
            'title' => 'Recherche de livres'
        ]);
    }

    /**
     * Change le statut de disponibilité d'un livre
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

        $newAvailability = !$book->isAvailable();
        $success = $this->bookManager->updateAvailability($id, $newAvailability);
        
        if ($success) {
            $statusText = $newAvailability ? 'disponible' : 'non disponible';
            Session::setFlash('success', 'Le livre est maintenant ' . $statusText . '.');
        } else {
            Session::setFlash('error', 'Erreur lors de la mise à jour de la disponibilité.');
        }
        
        $this->redirect('mon-compte');
    }

    /**
     * Gère l'upload d'une nouvelle image
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
     * Gère la mise à jour d'une image existante
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
}
