<?php

/**
 * Entité User
 * Représente un utilisateur de l'application
 */
class User extends Entity
{
    protected $id;
    private $username;
    private $email;
    private $password;
    private $avatar;
    private $login;
    private $created_at;
    private $updated_at;

    /**
     * Getters
     */
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

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Setters
     */
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

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * Vérifie si le mot de passe correspond au hash stocké
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hash un mot de passe
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
