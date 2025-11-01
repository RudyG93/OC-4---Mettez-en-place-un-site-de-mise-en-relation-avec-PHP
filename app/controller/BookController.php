<?php

/**
 * Contrôleur pour la gestion des livres
 */
class BookController extends Controller
{
    private BookManager $bookManager;
    private UserManager $userManager;

    public function __construct()
    {
        $this->bookManager = $this->loadManager('Book');
        $this->userManager = $this->loadManager('User');
    }

    /**
     * Affiche tous les livres disponibles (page publique)
     */
    public function index(): void
    {
        // Récupérer le terme de recherche s'il existe
        $searchTerm = $this->getQuery('q', '');
        
        // Exclure les livres de l'utilisateur connecté s'il est connecté
        $excludeUserId = null;
        if (Session::isLoggedIn()) {
            $excludeUserId = Session::getUserId();
        }

        // Si recherche, utiliser searchBooks, sinon tous les livres
        if (!empty($searchTerm)) {
            $books = $this->bookManager->searchBooks($searchTerm, $excludeUserId);
        } else {
            $books = $this->bookManager->findAvailableBooks($excludeUserId);
        }
        
        $this->render('book/index', [
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

        // Valider le token CSRF
        $this->validateCsrf('mon-compte');

        // Récupérer les données du formulaire
        $title = $this->getPost('title', '');
        $author = $this->getPost('author', '');
        $description = $this->getPost('description', '');
        $isAvailable = $this->getPost('is_available', '0');

        $errors = $this->validateBookData([
            'title' => $title,
            'author' => $author,
            'description' => $description
        ]);
        
        // Gestion de l'upload d'image
        $imageName = 'book_placeholder.png'; // Par défaut, utiliser le placeholder
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadedImage = $this->handleImageUpload($_FILES['image']);
            if ($uploadedImage) {
                $imageName = $uploadedImage;
            } else {
                $errors['image'] = 'Erreur lors de l\'upload de l\'image.';
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState([
                'title' => $title,
                'author' => $author,
                'description' => $description,
                'is_available' => $isAvailable
            ], $errors);
            $this->redirect('mon-compte#add-book-modal');
            return;
        }

        $bookData = [
            'user_id' => Session::getUserId(),
            'title' => $title,
            'author' => $author,
            'image' => $imageName,
            'description' => $description,
            'is_available' => $isAvailable === '1' ? 1 : 0
        ];

        $book = $this->bookManager->createBook($bookData);
        
        if ($book) {
            Session::setFlash('success', 'Livre ajouté avec succès !');
            $this->redirect('mon-compte');
        } else {
            Session::setFlash('error', 'Erreur lors de l\'ajout du livre.');
            $this->redirect('mon-compte#add-book-modal');
        }
    }

    /**
     * Affiche le détail d'un livre
     */
    public function show(int $id): void
    {
        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('nos-livres');
            return;
        }

        // Récupérer les infos du propriétaire
        $owner = $this->userManager->findById($book->getUserId());
        
        if (!$owner) {
            Session::setFlash('error', 'Propriétaire du livre introuvable.');
            $this->redirect('nos-livres');
            return;
        }

        // Récupérer d'autres livres du même propriétaire (max 4)
        $otherBooks = $this->bookManager->findByUserId($owner->getId(), 4);
        
        // Exclure le livre actuel de la liste des suggestions
        $otherBooks = array_filter($otherBooks, function($otherBook) use ($id) {
            return $otherBook->getId() !== $id;
        });
        
        // Limiter à 3 suggestions pour ne pas surcharger
        $otherBooks = array_slice($otherBooks, 0, 3);
        
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
        
        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('mon-compte');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire du livre
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect('mon-compte');
            return;
        }

        // Récupérer l'état du formulaire (anciennes valeurs et erreurs)
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

        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('mon-compte');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire du livre
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect('mon-compte');
            return;
        }

        // Valider le token CSRF
        $this->validateCsrf('book/' . $id . '/edit');

        // Récupérer les données du formulaire
        $title = $this->getPost('title', '');
        $author = $this->getPost('author', '');
        $description = $this->getPost('description', '');
        $isAvailable = $this->getPost('available', '0');

        $errors = $this->validateBookData([
            'title' => $title,
            'author' => $author,
            'description' => $description
        ]);

        // Gestion de l'upload d'image
        $imageName = $book->getImage(); // Conserver l'ancienne image par défaut
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImageName = $this->handleImageUpload($_FILES['image']);
            
            if ($newImageName) {
                // Supprimer l'ancienne image si elle existe et que ce n'est pas le placeholder
                if ($book->getImage() && $book->getImage() !== 'book_placeholder.png') {
                    $oldImagePath = 'uploads/books/' . $book->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imageName = $newImageName;
            } else {
                $errors['image'] = 'Erreur lors de l\'upload de l\'image.';
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState([
                'title' => $title,
                'author' => $author,
                'description' => $description,
                'available' => $isAvailable
            ], $errors);
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        $bookData = [
            'title' => $title,
            'author' => $author,
            'image' => $imageName,
            'description' => $description,
            'is_available' => $isAvailable === '1' ? 1 : 0
        ];

        $updatedBook = $this->bookManager->updateBook($id, $bookData);
        
        if ($updatedBook) {
            Session::setFlash('success', 'Livre modifié avec succès !');
            $this->redirect('mon-compte');
        } else {
            Session::setFlash('error', 'Erreur lors de la modification du livre.');
            $this->redirect('book/' . $id . '/edit');
        }
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

        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('mon-compte');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire du livre
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce livre.');
            $this->redirect('mon-compte');
            return;
        }

        // Valider le token CSRF
        $this->validateCsrf('mon-compte');

        $result = $this->bookManager->deleteBook($id);
        
        if ($result['success']) {
            // Supprimer l'image si elle existe
            if ($book->getImage()) {
                $imagePath = 'uploads/books/' . $book->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
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

        // Récupérer l'ID du livre depuis le POST
        $id = (int) $this->getPost('book_id', 0);
        
        if ($id === 0) {
            Session::setFlash('error', 'ID du livre invalide.');
            $this->redirect('mon-compte');
            return;
        }

        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('mon-compte');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire du livre
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect('mon-compte');
            return;
        }

        // Valider le token CSRF
        if (!isset($_POST['csrf_token']) || !Session::verifyCsrfToken($_POST['csrf_token'])) {
            Session::setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        // Supprimer le fichier image si ce n'est pas le placeholder
        if ($book->getImage() && $book->getImage() !== 'book_placeholder.png') {
            $imagePath = 'uploads/books/' . $book->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Mettre à jour le livre avec le placeholder
        $bookData = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'image' => 'book_placeholder.png',
            'description' => $book->getDescription(),
            'is_available' => $book->isAvailable() ? 1 : 0
        ];

        $updatedBook = $this->bookManager->updateBook($id, $bookData);
        
        if ($updatedBook) {
            Session::setFlash('success', 'Image supprimée avec succès !');
        } else {
            Session::setFlash('error', 'Erreur lors de la suppression de l\'image.');
        }
        
        $this->redirect('book/' . $id . '/edit');
    }

    /**
     * Recherche de livres
     */
    public function search(): void
    {
        $searchTerm = $this->getQuery('q', '');
        $books = [];
        
        if (!empty($searchTerm)) {
            // Exclure les livres de l'utilisateur connecté s'il est connecté
            $excludeUserId = null;
            if (Session::isLoggedIn()) {
                $excludeUserId = Session::getUserId();
            }
            
            $books = $this->bookManager->searchBooks($searchTerm, $excludeUserId);
        }
        
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

        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect('mon-compte');
            return;
        }

        // Vérifier que l'utilisateur est le propriétaire du livre
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect('mon-compte');
            return;
        }

        // Valider le token CSRF
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
     * Valide les données d'un livre
     */
    private function validateBookData(array $data): array
    {
        $errors = [];

        if (empty(trim($data['title'] ?? ''))) {
            $errors['title'] = 'Le titre est obligatoire.';
        } elseif (strlen(trim($data['title'])) > 255) {
            $errors['title'] = 'Le titre ne doit pas dépasser 255 caractères.';
        }

        if (empty(trim($data['author'] ?? ''))) {
            $errors['author'] = 'L\'auteur est obligatoire.';
        } elseif (strlen(trim($data['author'])) > 255) {
            $errors['author'] = 'L\'auteur ne doit pas dépasser 255 caractères.';
        }

        if (!empty($data['description']) && strlen(trim($data['description'])) > 1000) {
            $errors['description'] = 'La description ne doit pas dépasser 1000 caractères.';
        }

        return $errors;
    }

    /**
     * Gère l'upload d'une image de livre
     */
    private function handleImageUpload(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        if ($file['size'] > $maxSize) {
            return null;
        }
        
        $uploadDir = 'uploads/books/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_', true) . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $filename;
        }
        
        return null;
    }
}