<?php

/**
 * Contrôleur de gestion des erreurs
 * 
 * Gère l'affichage des pages d'erreur (404, etc.)
 */
class ErrorController extends Controller
{
    /**
     * Affiche la page 404 - Page non trouvée
     * 
     * @param string|null $message Message personnalisé (optionnel)
     * Route : Déclenchée automatiquement par App.php en cas d'erreur
     */
    public function notFound($message = null)
    {
        $data = [
            'title' => 'Page non trouvée - TomTroc',
            'errorCode' => '404',
            'errorMessage' => 'Page non trouvée',
            'errorDescription' => 'Désolé, la page que vous recherchez n\'existe pas ou a été déplacée.'
        ];
        
        $this->render('error/error', $data);
    }
}
