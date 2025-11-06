<?php

/*
 * Contrôleur d'authentification
 * 
 * Gère l'inscription, la connexion et la déconnexion des utilisateurs.
 * Assure la sécurité avec CSRF tokens et password hashing.
 * 
 * register() - Affiche le formulaire d'inscription et traite les soumissions
 * registerPost() - Traite les données d'inscription
 * login() - Affiche le formulaire de connexion et traite les soumissions
 * loginPost() - Traite les données de connexion
 * logout() - Déconnecte l'utilisateur
 * 
 */

class AuthController extends Controller
{
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    /* Afficher le formulaire d'inscription */

    public function register()
    {
        // Si la requête est POST, traiter le formulaire
        if ($this->isPost()) {
            $this->registerPost();
            return;
        }

        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Générer un token CSRF
        $csrfToken = Session::generateCsrfToken();

        $data = [
            'title' => 'Inscription - TomTroc',
            'csrfToken' => $csrfToken,
            'errors' => []
        ];

        $this->render('auth/register', $data);
    }

    /* Traiter le formulaire d'inscription */

    private function registerPost()
    {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Vérifier le token CSRF
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('danger', 'Token de sécurité invalide.');
            $this->redirect('register');
        }

        // Récupérer les données du formulaire
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Tableau pour stocker les erreurs
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

        // Validation de la confirmation du mot de passe
        if (empty($passwordConfirm)) {
            $errors['password_confirm'] = 'La confirmation du mot de passe est requise.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }

        // S'il y a des erreurs, réafficher le formulaire
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

        // Créer le nouvel utilisateur (hash du mot de passe avant insertion)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'bio' => null,
            'avatar' => null,
        ];

        $user = $this->userManager->createUser($userData);

        // Enregistrer en base de données
        if ($user) {
            Session::setFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            $this->redirect('login');
        } else {
            Session::setFlash('danger', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            $this->redirect('register');
        }
    }

    /* Afficher le formulaire de connexion */

    public function login()
    {
        // Si la requête est POST, traiter le formulaire
        if ($this->isPost()) {
            $this->loginPost();
            return;
        }

        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Générer un token CSRF
        $csrfToken = Session::generateCsrfToken();

        $data = [
            'title' => 'Connexion - TomTroc',
            'csrfToken' => $csrfToken
        ];

        $this->render('auth/login', $data);
    }

    /* Traiter le formulaire de connexion */

    private function loginPost()
    {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Session::isLoggedIn()) {
            $this->redirect('');
        }

        // Vérifier le token CSRF
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('danger', 'Token de sécurité invalide.');
            $this->redirect('login');
        }

        // Récupérer les données du formulaire
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation basique
        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Veuillez remplir tous les champs.');
            $this->redirect('login');
        }

        // Rechercher l'utilisateur
        $user = $this->userManager->getUserByEmail($email);

        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if (!$user || !$user->verifyPassword($password)) {
            Session::setFlash('danger', 'Email ou mot de passe incorrect.');
            $this->redirect('login');
        }

        // Connecter l'utilisateur
        // D'abord nettoyer la session des objets obsolètes
        Session::cleanSession();

        Session::set('user_id', $user->getId());
        Session::set('username', $user->getUsername());
        Session::setFlash('success', 'Bienvenue ' . $user->getUsername() . ' !');

        $this->redirect('');
    }

    /* Déconnexion */

    public function logout()
    {
        Session::destroy();
        Session::setFlash('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('');
    }
}
