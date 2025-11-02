<?php

class UserManager extends Model {

    /**
     * Récupère un utilisateur par son ID
     */
    public function findById($id) {
        $this->table = 'users';
        $row = parent::findById($id);
        return $this->hydrateEntity('User', $row);
    }

    /**
     * Récupère un utilisateur par son email
     */
    public function getUserByEmail($email) {
        $this->table = 'users';
        $row = parent::findOneBy(['email' => $email]);
        return $this->hydrateEntity('User', $row);
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur
     */
    public function getUserByUsername($username) {
        $this->table = 'users';
        $row = parent::findOneBy(['username' => $username]);
        return $this->hydrateEntity('User', $row);
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
        
        if (isset($userData['bio'])) {
            $fields[] = 'bio = :bio';
            $params[':bio'] = $userData['bio'];
        }
        
        if (isset($userData['avatar'])) {
            $fields[] = 'avatar = :avatar';
            $params[':avatar'] = $userData['avatar'];
        }
        
        if (isset($userData['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = $userData['password'];
        }
        
        $fields[] = 'updated_at = NOW()';
        
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
        $this->table = 'users';
        
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
        
        // Cas simple : utiliser findBy()
        $users = parent::findBy(['email' => $email], null, 1);
        return !empty($users);
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username, $excludeUserId = null) {
        $this->table = 'users';
        
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
        
        // Cas simple : utiliser findBy()
        $users = parent::findBy(['username' => $username], null, 1);
        return !empty($users);
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
}
