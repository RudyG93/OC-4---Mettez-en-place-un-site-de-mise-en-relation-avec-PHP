<?php
/**
 * HomeController
 * 
 * Gère l'affichage de la page d'accueil
 * 
 * @author TomTroc Team
 * @version 1.0.0
 */

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     * 
     * Récupère et affiche les 4 derniers livres ajoutés sur la plateforme
     * 
     * @return void
     */
    public function index()
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
