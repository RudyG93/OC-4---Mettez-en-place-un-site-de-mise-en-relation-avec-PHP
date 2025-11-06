<?php

/**
 * Entité Book - Représente un livre dans l'application
 * 
 * Propriétés :
 * - Identifiants : id, user_id
 * - Informations : title, author, description, image
 * - État : is_available
 * - Métadonnées : created_at
 */
class Book
{
    /* ================================
       PROPRIÉTÉS
       ================================ */

    private $id;
    private $user_id;
    private $title;
    private $author;
    private $image;
    private $description;
    private $is_available;
    private $created_at;

    /* ================================
       GETTERS - PROPRIÉTÉS DE BASE
       ================================ */
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

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /* ================================
       SETTERS
       ================================ */
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
}