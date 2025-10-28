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

    public function __construct()
    {
        $this->userManager = $this->loadManager('User');
        $this->bookManager = $this->loadManager('Book');
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

        // Afficher la vue
        $pageTitle = 'Mon profil';
        $activePage = 'account';

        $data = [
            'title' => $pageTitle,
            'user' => $user,
            'oldInput' => $formState['oldInput'],
            'errors' => $formState['errors']
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

        // Vérifier qu'un fichier a été uploadé
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Veuillez sélectionner une image.');
            $this->redirect('mon-compte');
        }

        $file = $_FILES['avatar'];

        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if (!in_array($file['type'], $allowedTypes)) {
            Session::setFlash('error', 'Format de fichier non autorisé. Utilisez JPG, PNG ou GIF.');
            $this->redirect('mon-compte');
        }

        if ($file['size'] > $maxSize) {
            Session::setFlash('error', 'Le fichier est trop volumineux (max 2 Mo).');
            $this->redirect('mon-compte');
        }

        // Créer le dossier uploads/avatars s'il n'existe pas
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;

        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Supprimer l'ancien avatar s'il existe
            $currentUser = $this->getCurrentUser();
            if ($currentUser->getAvatar()) {
                $oldAvatarPath = $uploadDir . $currentUser->getAvatar();
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            // Mettre à jour l'avatar dans la base de données
            $success = $this->userManager->updateUser($currentUser->getId(), [
                'username' => $currentUser->getUsername(),
                'email' => $currentUser->getEmail(),
                'avatar' => $filename
            ]);

            if ($success) {
                Session::setFlash('success', 'Votre avatar a été mis à jour avec succès.');
            } else {
                Session::setFlash('error', 'Erreur lors de la mise à jour de l\'avatar.');
            }
        } else {
            Session::setFlash('error', 'Erreur lors de l\'upload du fichier.');
        }

        $this->redirect('mon-compte');
    }
}
