<?php

/**
 * ProfileController - Gestion des profils utilisateurs
 * 
 * Permet de voir, modifier son profil et consulter les profils publics
 */

class ProfileController extends Controller
{
    private UserManager $userManager;
    private BookManager $bookManager;
    private ImageUploader $imageUploader;

    public function __construct()
    {
        $this->userManager = $this->loadManager('User');
        $this->bookManager = $this->loadManager('Book');
        $this->imageUploader = new ImageUploader();
    }

    /**
     * Affiche le profil de l'utilisateur connecté
     */
    public function view(): void
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();

        // Récupérer l'utilisateur connecté et vérifier qu'il existe
        $user = $this->ensureExists($this->getCurrentUser(), 'Profil introuvable.');

        // Récupérer l'état du formulaire (anciennes valeurs et erreurs)
        $formState = $this->getFormState();

        // Compter les livres de l'utilisateur
        $totalBooks = $this->bookManager->countUserBooks($user->getId());
        $availableBooks = $this->bookManager->countAvailableUserBooks($user->getId());

        // Récupérer tous les livres de l'utilisateur
        $userBooks = $this->bookManager->findByUserId($user->getId());

        // Afficher la vue
        $pageTitle = 'Mon profil';
        $activePage = 'account';

        $data = [
            'title' => $pageTitle,
            'user' => $user,
            'oldInput' => $formState['oldInput'],
            'errors' => $formState['errors'],
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'userBooks' => $userBooks
        ];

        $this->render('profile/view', $data);
    }

    /**
     * Traite la modification du profil
     */
    public function update(): void
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();

        // Vérifier que la requête est en POST
        if (!$this->isPost()) {
            $this->redirect('mon-compte');
        }

        // Valider le token CSRF
        $this->validateCsrf('mon-compte');

        // Récupérer les données du formulaire
        $username = $this->getPost('username', '');
        $email = $this->getPost('email', '');
        $password = $this->getPost('password', '');

        // Tableau d'erreurs
        $errors = [];

        // Validation du pseudo
        if (empty($username)) {
            $errors['username'] = 'Le pseudo est requis.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Le pseudo doit contenir entre 3 et 50 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors['username'] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.';
        }

        // Validation de l'email
        if (empty($email)) {
            $errors['email'] = 'L\'email est requis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide.';
        } else {
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $currentUser = $this->getCurrentUser();

            if ($email !== $currentUser->getEmail()) {
                $existingUser = $this->userManager->getUserByEmail($email);
                if ($existingUser) {
                    $errors['email'] = 'Cet email est déjà utilisé par un autre compte.';
                }
            }
        }

        // Validation du mot de passe (optionnel)
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState([
                'username' => $username,
                'email' => $email
            ], $errors);
            $this->redirect('mon-compte');
        }

        // Préparer les données à mettre à jour
        $data = [
            'username' => $username,
            'email' => $email
        ];

        // Ajouter le mot de passe s'il a été modifié
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Mettre à jour le profil
        $currentUser = $this->getCurrentUser();
        $success = $this->userManager->updateUser($currentUser->getId(), $data);

        if ($success) {
            // Mettre à jour les informations dans la session
            Session::set('username', $username);
            $this->success('Votre profil a été mis à jour avec succès.', 'mon-compte');
        } else {
            $this->error('Une erreur est survenue lors de la mise à jour du profil.', 'mon-compte/modifier');
        }
    }

    /**
     * Affiche le profil public d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur à afficher
     */
    public function show(int $userId): void
    {
        // Récupérer l'utilisateur
        $user = $this->userManager->findById($userId);

        // Vérifier que l'utilisateur existe
        $user = $this->ensureExists($user, 'Utilisateur introuvable.');

        // Récupérer les livres de l'utilisateur
        $bookManager = $this->loadManager('Book');
        $userBooks = $bookManager->findByUserId($userId);

        // Afficher la vue
        $data = [
            'title' => 'Profil de ' . $this->escape($user->getUsername()),
            'user' => $user,
            'userBooks' => $userBooks
        ];

        $this->render('profile/show', $data);
    }

    /**
     * Traite l'upload de l'avatar
     */
    public function updateAvatar(): void
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();

        // Vérifier que la requête est en POST
        if (!$this->isPost()) {
            $this->redirect('mon-compte');
        }

        // Valider le token CSRF
        $this->validateCsrf('mon-compte');

        // Vérifier qu'un fichier a été uploadé et l'uploader
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Veuillez sélectionner une image.');
            $this->redirect('mon-compte');
        }

        $uploadResult = $this->imageUploader->upload($_FILES['avatar'], 'avatar');

        if (!$uploadResult['success']) {
            Session::setFlash('error', $uploadResult['error']);
            $this->redirect('mon-compte');
        }

        // Récupérer l'utilisateur actuel
        $currentUser = $this->getCurrentUser();
        
        // Supprimer l'ancien avatar
        $this->imageUploader->delete($currentUser->getAvatar(), 'avatar');

        // Mettre à jour l'avatar dans la base de données
        $success = $this->userManager->updateUser($currentUser->getId(), [
            'username' => $currentUser->getUsername(),
            'email' => $currentUser->getEmail(),
            'avatar' => $uploadResult['filename']
        ]);

        if ($success) {
            Session::setFlash('success', 'Votre avatar a été mis à jour avec succès.');
        } else {
            Session::setFlash('error', 'Erreur lors de la mise à jour de l\'avatar.');
        }

        $this->redirect('mon-compte');
    }

    /**
     * Supprime l'avatar de l'utilisateur connecté
     */
    public function deleteAvatar(): void
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();

        // Vérifier que la requête est en POST
        if (!$this->isPost()) {
            $this->redirect('mon-compte');
        }

        // Valider le token CSRF
        $this->validateCsrf('mon-compte');

        // Récupérer l'utilisateur actuel
        $currentUser = $this->getCurrentUser();

        // Vérifier qu'il a bien un avatar
        if (!$currentUser->getAvatar()) {
            Session::setFlash('error', 'Aucune photo de profil à supprimer.');
            $this->redirect('mon-compte');
        }

        // Supprimer le fichier physique
        $this->imageUploader->delete($currentUser->getAvatar(), 'avatar');

        // Mettre à jour la base de données avec le placeholder
        $success = $this->userManager->updateUser($currentUser->getId(), [
            'username' => $currentUser->getUsername(),
            'email' => $currentUser->getEmail(),
            'avatar' => $this->imageUploader->getPlaceholder('avatar')
        ]);

        if ($success) {
            Session::setFlash('success', 'Votre photo de profil a été supprimée avec succès.');
        } else {
            Session::setFlash('error', 'Erreur lors de la suppression de la photo de profil.');
        }

        $this->redirect('mon-compte');
    }
}
