<?php

/**
 * Entité Book
 * Représente un livre de l'application
 */
class Book extends Entity
{
    protected $id;
    private $user_id;
    private $title;
    private $author;
    private $image;
    private $description;
    private $is_available;
    private $created_at;
    private $updated_at;

    /**
     * Getters
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getIsAvailable()
    {
        return $this->is_available;
    }

    public function isAvailable()
    {
        return (bool) $this->is_available;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Setters
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setIsAvailable($is_available)
    {
        $this->is_available = (int) $is_available;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * Méthodes utilitaires
     */
    
    /**
     * Retourne le chemin complet vers l'image
     */
    public function getImagePath()
    {
        if ($this->image) {
            return BASE_URL . 'uploads/books/' . $this->image;
        }
        return null;
    }

    /**
     * Retourne une description tronquée
     */
    public function getShortDescription($length = 100)
    {
        if (!$this->description) {
            return '';
        }
        
        if (strlen($this->description) <= $length) {
            return $this->description;
        }
        
        return substr($this->description, 0, $length) . '...';
    }

    /**
     * Retourne le statut de disponibilité en français
     */
    public function getAvailabilityText()
    {
        return $this->isAvailable() ? 'Disponible' : 'Non disponible';
    }

    /**
     * Retourne la classe CSS pour le badge de disponibilité
     */
    public function getAvailabilityClass()
    {
        return $this->isAvailable() ? 'availability-available' : 'availability-unavailable';
    }
}