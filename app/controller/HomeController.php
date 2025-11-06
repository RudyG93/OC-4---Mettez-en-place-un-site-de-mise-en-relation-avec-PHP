<?php

/**
 * Contrôleur de la page d'accueil
 * 
 * Gère l'affichage de la page d'accueil avec les derniers livres ajoutés.
 */
class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     * Récupère et affiche les 4 derniers livres ajoutés sur la plateforme
     * 
     * Route : / (home)
     */
    public function index() : void
    {
        // Récupérer les 4 derniers livres ajoutés
        $bookManager = new BookManager();
        $latestBooks = $bookManager->getLatestBooks(4);
        
        $data = [
            'title' => 'Accueil - TomTroc',
            'latestBooks' => $latestBooks
        ];
        
        $this->render('home/index', $data);
    }
}
