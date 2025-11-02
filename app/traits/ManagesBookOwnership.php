<?php

/**
 * Trait pour gérer les vérifications de propriété des livres
 * 
 * Factorise les vérifications répétitives :
 * - Existence du livre
 * - Propriété du livre par l'utilisateur connecté
 */
trait ManagesBookOwnership
{
    /**
     * Récupère un livre et vérifie qu'il existe
     * 
     * @param int $id ID du livre
     * @param string $redirectUrl URL de redirection en cas d'erreur
     * @return Book|null Retourne le livre ou null si erreur (avec redirection)
     */
    protected function findBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
    {
        $book = $this->bookManager->findById($id);
        
        if (!$book) {
            Session::setFlash('error', 'Livre introuvable.');
            $this->redirect($redirectUrl);
            return null;
        }
        
        return $book;
    }

    /**
     * Vérifie que l'utilisateur connecté est le propriétaire du livre
     * 
     * @param Book $book Livre à vérifier
     * @param string $redirectUrl URL de redirection si non propriétaire
     * @return bool True si propriétaire, false sinon (avec redirection)
     */
    protected function ensureBookOwnership(Book $book, string $redirectUrl = 'mon-compte'): bool
    {
        if ($book->getUserId() !== Session::getUserId()) {
            Session::setFlash('error', 'Vous n\'êtes pas autorisé à modifier ce livre.');
            $this->redirect($redirectUrl);
            return false;
        }
        
        return true;
    }

    /**
     * Récupère un livre et vérifie que l'utilisateur en est le propriétaire
     * 
     * Méthode de commodité qui combine findBookOrFail() et ensureBookOwnership()
     * 
     * @param int $id ID du livre
     * @param string $redirectUrl URL de redirection en cas d'erreur
     * @return Book|null Retourne le livre ou null si erreur (avec redirection)
     */
    protected function findOwnBookOrFail(int $id, string $redirectUrl = 'mon-compte'): ?Book
    {
        $book = $this->findBookOrFail($id, $redirectUrl);
        
        if (!$book) {
            return null;
        }
        
        if (!$this->ensureBookOwnership($book, $redirectUrl)) {
            return null;
        }
        
        return $book;
    }
}
