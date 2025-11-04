<?php
/**
 * Classe Entity - Entité de base
 * 
 * Toutes les entités métier (User, Book, Message) héritent de cette classe.
 * Fournit la méthode hydrate() pour remplir automatiquement les propriétés
 * à partir d'un tableau de données (typiquement depuis la BDD).
 */

abstract class Entity
{
    protected $id;
    
    /**
     * Hydrate l'objet à partir d'un tableau de données
     * 
     * Remplit automatiquement les propriétés de l'objet en appelant
     * les setters correspondants. Convertit les noms de colonnes BDD
     * (snake_case) en noms de méthodes PHP (camelCase).
     * 
     * Exemple : 'created_at' → 'setCreatedAt()'
     * 
     * @param array $data Tableau associatif (clé => valeur)
     */
    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            // Ignorer les propriétés obsolètes
            if ($key === 'login') {
                continue;
            }
            
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            // Ignorer silencieusement les propriétés qui n'ont pas de setter
        }
    }
    
    /**
     * Getters et Setters pour l'ID
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }
}
