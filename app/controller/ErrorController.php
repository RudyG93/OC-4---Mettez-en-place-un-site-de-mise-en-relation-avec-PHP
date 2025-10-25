<?php
/**
 * ErrorController - Gère les pages d'erreur
 */

class ErrorController extends Controller
{
    /**
     * Page 404 - Page non trouvée
     */
    public function notFound($message = null)
    {
        $data = [
            'title' => 'Page non trouvée - TomTroc',
            'errorCode' => '404',
            'errorMessage' => 'Page non trouvée',
            'errorDescription' => 'Désolé, la page que vous recherchez n\'existe pas ou a été déplacée.'
        ];
        
        $this->render('error/404', $data);
    }
    
    /**
     * Page 403 - Accès interdit
     */
    public function forbidden()
    {
        http_response_code(403);
        
        $data = [
            'title' => 'Accès interdit - TomTroc',
            'errorCode' => '403',
            'errorMessage' => 'Accès interdit',
            'errorDescription' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'
        ];
        
        $this->render('error/403', $data);
    }
}
