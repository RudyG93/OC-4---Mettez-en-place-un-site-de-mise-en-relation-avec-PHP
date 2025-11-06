<?php

/**
 * Contrôleur de gestion des profils utilisateurs
 * 
 * Gère l'affichage et la modification des profils :
 * - Profil privé (mon compte)
 * - Profils publics
 * - Gestion de l'avatar
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

    /* ================================
       ACTIONS - PROFIL PRIVÉ
       ================================ */

    /**
     * Affiche le profil de l'utilisateur connecté (page Mon compte)
     * Route : mon-compte
     */
    public function view(): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();

        // Statistiques des livres
        $totalBooks = $this->bookManager->countUserBooks($user->getId());
        $availableBooks = $this->bookManager->countAvailableUserBooks($user->getId());
        $userBooks = $this->bookManager->findByUserId($user->getId());

        $this->render('profile/account', [
            'title' => 'Mon profil',
            'user' => $user,
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'userBooks' => $userBooks
        ]);
    }

    /**
     * Traite la modification du profil (POST)
     * Route : mon-compte/update
     */
    public function update(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $this->validateCsrf('mon-compte');

        $user = $this->getCurrentUser();

        // Récupérer et valider les données
        $data = [
            'username' => $this->getPost('username', ''),
            'email' => $this->getPost('email', ''),
            'password' => $this->getPost('password', '')
        ];

        $errors = $this->validateProfileData($data, $user);

        if (!empty($errors)) {
            Session::setFlash('error', implode(', ', $errors));
            $this->redirect('mon-compte');
            return;
        }

        // Préparer les données à mettre à jour
        $updateData = [
            'username' => $data['username'],
            'email' => $data['email']
        ];

        // Ajouter le mot de passe s'il a été modifié
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Mettre à jour le profil
        $success = $this->userManager->updateUser($user->getId(), $updateData);

        if (!$success) {
            Session::setFlash('error', 'Une erreur est survenue lors de la mise à jour du profil.');
            $this->redirect('mon-compte');
            return;
        }

        // Mettre à jour la session
        Session::set('username', $data['username']);
        Session::setFlash('success', 'Votre profil a été mis à jour avec succès.');
        $this->redirect('mon-compte');
    }

    /* ================================
       ACTIONS - PROFIL PUBLIC
       ================================ */

    /**
     * Affiche le profil public d'un utilisateur
     * Route : profil/{id}
     */
    public function show(int $userId): void
    {
        $user = $this->userManager->findById($userId);

        if (!$user) {
            Session::setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('');
            return;
        }

        // Récupérer les livres de l'utilisateur
        $userBooks = $this->bookManager->findByUserId($userId);

        $this->render('profile/public', [
            'title' => 'Profil de ' . escape($user->getUsername()),
            'user' => $user,
            'userBooks' => $userBooks
        ]);
    }

    /* ================================
       ACTIONS - GESTION AVATAR
       ================================ */

    /**
     * Traite l'upload de l'avatar (POST)
     * Route : mon-compte/update-avatar
     */
    public function updateAvatar(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $this->validateCsrf('mon-compte');

        // Vérifier qu'un fichier a été uploadé
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Veuillez sélectionner une image.');
            $this->redirect('mon-compte');
            return;
        }

        // Uploader la nouvelle image
        $uploadResult = $this->imageUploader->upload($_FILES['avatar'], 'avatar');

        if (!$uploadResult['success']) {
            Session::setFlash('error', $uploadResult['error']);
            $this->redirect('mon-compte');
            return;
        }

        $user = $this->getCurrentUser();

        // Supprimer l'ancien avatar (le placeholder sera automatiquement ignoré)
        $this->imageUploader->delete($user->getAvatar(), 'avatar');

        // Mettre à jour l'avatar en base
        $success = $this->userManager->updateUser($user->getId(), [
            'avatar' => $uploadResult['filename']
        ]);

        $message = $success ? 'Votre avatar a été mis à jour avec succès.' : 'Erreur lors de la mise à jour de l\'avatar.';
        $type = $success ? 'success' : 'error';

        Session::setFlash($type, $message);
        $this->redirect('mon-compte');
    }

    /**
     * Supprime l'avatar de l'utilisateur connecté (POST)
     * Route : mon-compte/delete-avatar
     */
    public function deleteAvatar(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('mon-compte');
            return;
        }

        $this->validateCsrf('mon-compte');

        $user = $this->getCurrentUser();

        // Supprimer le fichier physique
        $this->imageUploader->delete($user->getAvatar(), 'avatar');

        // Mettre à jour avec le placeholder
        $success = $this->userManager->updateUser($user->getId(), [
            'avatar' => $this->imageUploader->getPlaceholder('avatar')
        ]);

        $message = $success ? 'Votre photo de profil a été supprimée avec succès.' : 'Erreur lors de la suppression de la photo de profil.';
        $type = $success ? 'success' : 'error';

        Session::setFlash($type, $message);
        $this->redirect('mon-compte');
    }

    /* ================================
       MÉTHODES INTERNES - HELPERS
       ================================ */

    /**
     * Récupère l'utilisateur connecté
     */
    private function getCurrentUser(): User
    {
        $user = $this->userManager->findById(Session::getUserId());

        if (!$user) {
            Session::setFlash('error', 'Profil introuvable.');
            $this->redirect('');
        }

        return $user;
    }

    /**
     * Valide les données du profil
     */
    private function validateProfileData(array $data, User $currentUser): array
    {
        $errors = [];

        // Validation du pseudo
        $username = trim($data['username']);
        if (empty($username)) {
            $errors[] = 'Le pseudo est requis.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Le pseudo doit contenir entre 3 et 50 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.';
        }

        // Validation de l'email
        $email = trim($data['email']);
        if (empty($email)) {
            $errors[] = 'L\'email est requis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide.';
        } elseif ($email !== $currentUser->getEmail()) {
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $existingUser = $this->userManager->getUserByEmail($email);
            if ($existingUser) {
                $errors[] = 'Cet email est déjà utilisé par un autre compte.';
            }
        }

        // Validation du mot de passe (optionnel)
        $password = $data['password'];
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        return $errors;
    }
}
