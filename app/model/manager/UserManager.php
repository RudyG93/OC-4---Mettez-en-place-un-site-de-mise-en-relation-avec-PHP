<?php

class UserManager extends Model {

    /**
     * Récupère un utilisateur par son ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $user = new User();
            $user->hydrate($row);
            return $user;
        }
        
        return null;
    }

    /**
     * Récupère un utilisateur par son email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $user = new User();
            $user->hydrate($row);
            return $user;
        }
        
        return null;
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur
     */
    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        $statement->execute();
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $user = new User();
            $user->hydrate($row);
            return $user;
        }
        
        return null;
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function createUser($userData) {
        $sql = "
            INSERT INTO users (username, email, password, bio, avatar, created_at, updated_at) 
            VALUES (:username, :email, :password, :bio, :avatar, NOW(), NOW())
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', $userData['username'], PDO::PARAM_STR);
        $statement->bindValue(':email', $userData['email'], PDO::PARAM_STR);
        $statement->bindValue(':password', $userData['password'], PDO::PARAM_STR);
        $statement->bindValue(':bio', $userData['bio'] ?? null, PDO::PARAM_STR);
        $statement->bindValue(':avatar', $userData['avatar'] ?? null, PDO::PARAM_STR);
        
        if ($statement->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Met à jour un utilisateur
     */
    public function updateUser($id, $userData) {
        $sql = "
            UPDATE users 
            SET username = :username, 
                email = :email, 
                bio = :bio, 
                avatar = :avatar, 
                updated_at = NOW()
            WHERE id = :id
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':username', $userData['username'], PDO::PARAM_STR);
        $statement->bindValue(':email', $userData['email'], PDO::PARAM_STR);
        $statement->bindValue(':bio', $userData['bio'] ?? null, PDO::PARAM_STR);
        $statement->bindValue(':avatar', $userData['avatar'] ?? null, PDO::PARAM_STR);
        
        return $statement->execute();
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     */
    public function updatePassword($id, $hashedPassword) {
        $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
        
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        
        return $statement->execute();
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
        }

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        
        if ($excludeUserId) {
            $statement->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
        }

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        
        if ($excludeUserId) {
            $statement->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Récupère tous les utilisateurs (pour l'administration future)
     */
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        
        $users = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->hydrate($row);
            $users[] = $user;
        }
        
        return $users;
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }
        
        return null;
    }

    /**
     * Recherche des utilisateurs par nom d'utilisateur
     */
    public function searchUsers($query, $limit = 10) {
        $sql = "
            SELECT * FROM users 
            WHERE username LIKE :query 
            ORDER BY username ASC 
            LIMIT :limit
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        
        $users = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->hydrate($row);
            $users[] = $user;
        }
        
        return $users;
    }

    /**
     * Compte le nombre d'utilisateurs actifs
     */
    public function getActiveUsersCount() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Récupère les statistiques d'un utilisateur
     */
    public function getUserStats($userId) {
        $sql = "
            SELECT 
                u.*,
                (SELECT COUNT(*) FROM books WHERE user_id = u.id) as total_books,
                (SELECT COUNT(*) FROM books WHERE user_id = u.id AND is_available = 1) as available_books,
                (SELECT COUNT(*) FROM messages WHERE sender_id = u.id) as messages_sent,
                (SELECT COUNT(*) FROM messages WHERE recipient_id = u.id) as messages_received
            FROM users u 
            WHERE u.id = :user_id
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $user = new User();
            $user->hydrate($row);
            return $user;
        }
        
        return null;
    }
}