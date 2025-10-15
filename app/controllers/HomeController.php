<?php
/**
 * HomeController - Gère la page d'accueil
 */

class HomeController extends Controller
{
    /**
     * Page d'accueil - affiche Hello World pour commencer
     */
    public function index()
    {
        $data = [
            'title' => 'Accueil - TomTroc',
            'heading' => 'Hello World !',
            'message' => 'Bienvenue sur TomTroc, votre plateforme d\'échange de livres entre particuliers.',
            'features' => [
                'Architecture MVC respectée',
                'Routage fonctionnel',
                'Base de données SQL configurée',
                'Système de sessions',
                'Pattern Entité/Manager'
            ]
        ];
        
        $this->render('home/index', $data);
    }
}
