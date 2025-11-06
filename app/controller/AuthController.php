<?php

/**
 * Contrôleur d'authentification
 * 
 * Gère l'inscription, la connexion et la déconnexion des utilisateurs.
 * Assure la sécurité avec :
 * - Protection CSRF sur tous les formulaires
 * - Hashage des mots de passe (password_hash/password_verify)
 * - Validation complète des données d'inscription
 * - Méthode privée authenticate() pour centraliser la logique d'authentification
 */

class AuthController extends Controller
{
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    /* ================================
       ACTIONS - INSCRIPTION
       ================================ */

    /**
     * Affiche le formulaire d'inscription et traite la soumission
     * 
     * Validation :
     * - Pseudo : 3-50 caractères, alphanumériques uniquement
     * - Email : format valide et unique
     * - Mot de passe : minimum 6 caractères
     * - Confirmation : doit correspondre au mot de passe
     * 
     * Route : register
     */
    public function register(): void
    {
        // Si déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Traitement du formulaire (POST)
        if ($this->isPost()) {
            $this->validateCsrf('register');

            // Récupération des données
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $errors = [];

            // Validation du pseudo
            if (empty($username)) {
                $errors['username'] = 'Le pseudo est requis.';
            } elseif (strlen($username) < 3) {
                $errors['username'] = 'Le pseudo doit contenir au moins 3 caractères.';
            } elseif (strlen($username) > 50) {
                $errors['username'] = 'Le pseudo ne peut pas dépasser 50 caractères.';
            } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
                $errors['username'] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.';
            } elseif ($this->userManager->usernameExists($username)) {
                $errors['username'] = 'Ce pseudo est déjà utilisé.';
            }

            // Validation de l'email
            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide.';
            } elseif ($this->userManager->emailExists($email)) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }

            // Validation du mot de passe
            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            // Validation de la confirmation
            if (empty($passwordConfirm)) {
                $errors['password_confirm'] = 'La confirmation du mot de passe est requise.';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }

            // Si erreurs, réafficher le formulaire
            if (!empty($errors)) {
                $csrfToken = Session::generateCsrfToken();

                $data = [
                    'title' => 'Inscription - TomTroc',
                    'csrfToken' => $csrfToken,
                    'errors' => $errors
                ];

                $this->render('auth/register', $data);
                return;
            }

            // Créer le nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'avatar' => null,
            ];

            $user = $this->userManager->createUser($userData);

            if ($user) {
                Session::setFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                $this->redirect('login');
            } else {
                Session::setFlash('danger', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
                $this->redirect('register');
            }
        }

        // Affichage du formulaire (GET)
        $csrfToken = Session::generateCsrfToken();

        $data = [
            'title' => 'Inscription - TomTroc',
            'csrfToken' => $csrfToken,
            'errors' => []
        ];

        $this->render('auth/register', $data);
    }

    /* ================================
       ACTIONS - CONNEXION
       ================================ */

    /**
     * Affiche le formulaire de connexion et traite la soumission
     * 
     * Utilise la méthode authenticate() pour vérifier les identifiants.
     * Crée une session utilisateur en cas de succès.
     * 
     * Route : login
     */
    public function login(): void
    {
        // Si déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Traitement du formulaire (POST)
        if ($this->isPost()) {
            $this->validateCsrf('login');

            // Récupération des données
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation basique
            if (empty($email) || empty($password)) {
                Session::setFlash('danger', 'Veuillez remplir tous les champs.');
                $this->redirect('login');
            }

            // Authentification
            $user = $this->authenticate($email, $password);

            if (!$user) {
                Session::setFlash('danger', 'Email ou mot de passe incorrect.');
                $this->redirect('login');
            }

            // Connexion réussie : créer la session
            Session::cleanSession();
            Session::login($user->getId());
            Session::set('username', $user->getUsername());
            Session::setFlash('success', 'Bienvenue ' . $user->getUsername() . ' !');

            $this->redirect('');
        }

        // Affichage du formulaire (GET)
        $csrfToken = Session::generateCsrfToken();

        $data = [
            'title' => 'Connexion - TomTroc',
            'csrfToken' => $csrfToken
        ];

        $this->render('auth/login', $data);
    }

    /* ================================
       ACTIONS - DÉCONNEXION
       ================================ */

    /**
     * Déconnecte l'utilisateur et détruit sa session
     * 
     * Route : logout
     */
    public function logout(): void
    {
        Session::logout();
        Session::setFlash('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('');
    }

    /* ================================
       MÉTHODES INTERNES - AUTHENTIFICATION
       ================================ */

    /**
     * Authentifie un utilisateur avec son email et mot de passe
     * 
     * Combine la récupération de l'utilisateur (UserManager) et la vérification
     * du mot de passe hashé (password_verify).
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return User|null L'utilisateur si authentification réussie, null sinon
     */
    private function authenticate(string $email, string $password): ?User
    {
        $user = $this->userManager->getUserByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }

        return null;
    }
}
