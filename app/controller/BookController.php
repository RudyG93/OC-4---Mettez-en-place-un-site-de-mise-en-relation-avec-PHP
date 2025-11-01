<?php

/**
 * Contrôleur pour la gestion des livres
 * 
 * Refactorisé pour utiliser les services BookValidator et ImageUploader
 */
class BookController extends Controller
{
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

        // Récupérer et valider les données du formulaire
        $data = [
            'title' => $this->getPost('title', ''),
            'author' => $this->getPost('author', ''),
            'description' => $this->getPost('description', ''),
            'is_available' => $this->getPost('is_available', '0')
        ];

        $errors = $this->validator->validate($data);
        
        // Gestion de l'upload d'image
        $imageName = $this->imageUploader->getPlaceholder('book');
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->imageUploader->upload($_FILES['image'], 'book');
            
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState($data, $errors);
            $this->redirect('mon-compte#add-book-modal');
            return;
        }

        // Préparer les données nettoyées
        $cleanData = $this->validator->sanitize($data);
        $bookData = array_merge($cleanData, [
            'user_id' => Session::getUserId(),
            'image' => $imageName
        ]);

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

        // Récupérer et valider les données du formulaire
        $data = [
            'title' => $this->getPost('title', ''),
            'author' => $this->getPost('author', ''),
            'description' => $this->getPost('description', ''),
            'is_available' => $this->getPost('available', '0')
        ];

        $errors = $this->validator->validate($data);

        // Gestion de l'upload d'image
        $imageName = $book->getImage(); // Conserver l'ancienne image par défaut
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->imageUploader->upload($_FILES['image'], 'book');
            
            if ($uploadResult['success']) {
                // Supprimer l'ancienne image
                $this->imageUploader->delete($book->getImage(), 'book');
                $imageName = $uploadResult['filename'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState($data, $errors);
            $this->redirect('book/' . $id . '/edit');
            return;
        }

        // Préparer les données nettoyées
        $cleanData = $this->validator->sanitize($data);
        $bookData = array_merge($cleanData, [
            'image' => $imageName
        ]);

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
            // Supprimer l'image
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

        // Supprimer le fichier image
        $this->imageUploader->delete($book->getImage(), 'book');

        // Mettre à jour le livre avec le placeholder
        $bookData = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'image' => $this->imageUploader->getPlaceholder('book'),
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
}
