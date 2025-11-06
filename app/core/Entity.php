<?php
/**
 * Classe Entity - Entité de base
 * 
 * Toutes les entités métier (User, Book, Message) héritent de cette classe.
 */

abstract class Entity
{
    protected $id;
    
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
