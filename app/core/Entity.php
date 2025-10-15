<?php
/**
 * Classe Entity - Entité de base
 * Toutes les entités métier héritent de cette classe
 */

abstract class Entity
{
    protected $id;
    
    /**
     * Hydrate l'objet à partir d'un tableau de données
     */
    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
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
    }
}
