<?php

/**
 * UserManager
 * Gère les opérations CRUD pour les utilisateurs
 */
class UserManager extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
    }

    /**
     * Récupérer un utilisateur par son ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            $user = new User();
            $user->hydrate($result);
            return $user;
        }
        return null;
    }

    /**
     * Récupérer un utilisateur par son email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            $user = new User();
            $user->hydrate($result);
            return $user;
        }
        return null;
    }

    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists($email)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifier si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUser($username, $email, $password)
    {
        $hashedPassword = User::hashPassword($password);
        
        $sql = "INSERT INTO {$this->table} (username, email, password, created_at) 
                VALUES (:username, :email, :password, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->findById($this->db->lastInsertId());
        }
        
        return false;
    }
}
