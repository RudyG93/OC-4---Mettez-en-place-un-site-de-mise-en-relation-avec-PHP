<?php

/**
 * Entité User - Représente un utilisateur de l'application
 * 
 * Propriétés :
 * - Identifiants : id, email, password
 * - Profil : username, avatar
 * - Métadonnées : created_at
 */
class User
{
    /* ================================
       PROPRIÉTÉS
       ================================ */

    private $id;
    private $username;
    private $email;
    private $password;
    private $avatar;
    private $created_at;

    /* ================================
       GETTERS
       ================================ */
    public function getId()
    {
        return $this->id;
    }

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

    public function getAvatar()
    {
        return $this->avatar;
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
}
