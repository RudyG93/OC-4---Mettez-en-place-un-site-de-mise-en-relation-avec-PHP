<?php

/**
 * UserManager - Gestion des utilisateurs en base de données
 * 
 * Responsabilités :
 * - CRUD des utilisateurs
 * - Authentification
 * - Vérifications d'unicité (email, username)
 */
class UserManager
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ================================
       LECTURE - PAR CRITÈRE
       ================================ */

    /**
     * Récupère un utilisateur par son ID
     * 
     * @param int $id ID de l'utilisateur
     * @return User|null L'utilisateur ou null si introuvable
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        $user = new User();
        $user->setId($row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setAvatar($row['avatar'] ?? null);
        $user->setCreatedAt($row['created_at']);
        
        return $user;
    }

    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email Email de l'utilisateur
     * @return User|null L'utilisateur ou null si introuvable
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        $user = new User();
        $user->setId($row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setAvatar($row['avatar'] ?? null);
        $user->setCreatedAt($row['created_at']);
        
        return $user;
    }

    /* ================================
       CRÉATION
       ================================ */

    /**
     * Crée un nouvel utilisateur
     * 
     * @param array $userData Données de l'utilisateur (username, email, password, avatar)
     * @return int|false ID de l'utilisateur créé ou false en cas d'échec
     */
    public function createUser($userData)
    {
        $sql = "
            INSERT INTO users (username, email, password, avatar, created_at) 
            VALUES (:username, :email, :password, :avatar, NOW())
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', $userData['username'], PDO::PARAM_STR);
        $statement->bindValue(':email', $userData['email'], PDO::PARAM_STR);
        $statement->bindValue(':password', $userData['password'], PDO::PARAM_STR);
        $statement->bindValue(':avatar', $userData['avatar'] ?? null, PDO::PARAM_STR);
        
        if ($statement->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /* ================================
       MODIFICATION
       ================================ */

    /**
     * Met à jour un utilisateur (mise à jour partielle supportée)
     * 
     * @param int $id ID de l'utilisateur
     * @param array $userData Données à mettre à jour (champs optionnels)
     * @return bool True si succès
     */
    public function updateUser($id, $userData) {
        // Construire la requête dynamiquement selon les champs présents
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($userData['username'])) {
            $fields[] = 'username = :username';
            $params[':username'] = $userData['username'];
        }
        
        if (isset($userData['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $userData['email'];
        }
        
        if (isset($userData['avatar'])) {
            $fields[] = 'avatar = :avatar';
            $params[':avatar'] = $userData['avatar'];
        }
        
        if (isset($userData['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = $userData['password'];
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $statement = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            if ($key === ':id') {
                $statement->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $statement->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        
        return $statement->execute();
    }

    /* ================================
       VÉRIFICATIONS - UNICITÉ
       ================================ */

    /**
     * Vérifie si un email existe déjà en base de données
     * 
     * @param string $email Email à vérifier
     * @param int|null $excludeUserId ID d'utilisateur à exclure (pour les mises à jour)
     * @return bool True si l'email existe déjà
     */
    public function emailExists($email, $excludeUserId = null) {
        if ($excludeUserId) {
            // Pour les cas avec exclusion, garder SQL manuel pour performance
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :exclude_id";
            $statement = $this->db->prepare($sql);
            $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        }
        
        // Cas simple : vérifier si l'email existe
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà en base de données
     * 
     * @param string $username Nom d'utilisateur à vérifier
     * @param int|null $excludeUserId ID d'utilisateur à exclure (pour les mises à jour)
     * @return bool True si le nom d'utilisateur existe déjà
     */
    public function usernameExists($username, $excludeUserId = null) {
        if ($excludeUserId) {
            // Pour les cas avec exclusion, garder SQL manuel pour performance
            $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username AND id != :exclude_id";
            $statement = $this->db->prepare($sql);
            $statement->bindValue(':username', $username, PDO::PARAM_STR);
            $statement->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        }
        
        // Cas simple : vérifier si le username existe
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
