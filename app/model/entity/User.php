<?php

/**
 * Entité User
 * Représente un utilisateur de l'application
 */
class User extends Entity
{
    private $username;
    private $email;
    private $password;
    private $bio;
    private $avatar;
    private $created_at;
    private $updated_at;

    /**
     * Empêche la création de propriétés dynamiques
     */
    public function __set($name, $value)
    {
        // Ignorer silencieusement les tentatives de création de propriétés obsolètes
        if ($name === 'login') {
            return;
        }
        
        // Pour les autres propriétés, lever une exception explicite
        throw new Exception("Propriété '$name' non autorisée dans la classe User");
    }

    /**
     * Getters
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Vérifie si l'utilisateur a une bio
     */
    public function hasBio()
    {
        return !empty($this->bio);
    }

    public function getAvatar()
    {
        return $this->avatar;
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
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setBio($bio)
    {
        $this->bio = $bio;
        return $this;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
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
     * Vérifie si le mot de passe correspond au hash stocké
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }
}
