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

        // Afficher la vue
        $pageTitle = 'Mon profil';
        $activePage = 'account';

        $data = [
            'title' => $pageTitle,
            'user' => $user
        ];

        $this->render('profile/view', $data);
    }

    /**
     * Affiche le formulaire de modification du profil
     */
    public function edit(): void
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireAuth();

        // Récupérer l'utilisateur connecté et vérifier qu'il existe
        $user = $this->ensureExists($this->getCurrentUser(), 'Profil introuvable.');

        // Récupérer l'état du formulaire (anciennes valeurs et erreurs)
        $formState = $this->getFormState();

        // Afficher la vue
        $data = [
            'title' => 'Modifier mon profil',
            'user' => $user,
            'csrfToken' => $this->getCsrfToken(),
            'oldInput' => $formState['oldInput'],
            'errors' => $formState['errors']
        ];

        $this->render('profile/edit', $data);
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
            $this->redirect('mon-compte/modifier');
        }

        // Valider le token CSRF
        $this->validateCsrf('mon-compte/modifier');

        // Récupérer les données du formulaire
        $username = $this->getPost('username', '');
        $email = $this->getPost('email', '');
        $password = $this->getPost('password', '');
        $passwordConfirm = $this->getPost('password_confirm', '');

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
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }
        }

        // S'il y a des erreurs, retourner au formulaire
        if (!empty($errors)) {
            $this->saveFormState([
                'username' => $username,
                'email' => $email
            ], $errors);
            $this->redirect('mon-compte/modifier');
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

        // Afficher la vue
        $data = [
            'title' => 'Profil de ' . $this->escape($user->getUsername()),
            'user' => $user
        ];

        $this->render('profile/show', $data);
    }
}
