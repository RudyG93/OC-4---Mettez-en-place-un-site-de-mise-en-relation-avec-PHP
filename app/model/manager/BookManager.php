<?php

/**
 * Manager pour la gestion des livres
 */
class BookManager extends Model
{
    /**
     * Récupère tous les livres d'un utilisateur
     */
    public function findByUserId($userId)
    {
        $this->table = 'books';
        $rows = parent::findBy(['user_id' => $userId], 'created_at DESC');
        return $this->hydrateEntities('Book', $rows);
    }

    /**
     * Récupère un livre par son ID
     */
    public function findById($id)
    {
        $this->table = 'books';
        $bookData = parent::findById($id);
        return $this->hydrateEntity('Book', $bookData);
    }

    /**
     * Récupère tous les livres disponibles (pour les échanges)
     */
    public function findAvailableBooks($excludeUserId = null)
    {
        $sql = "SELECT b.id, b.user_id, b.title, b.author, b.image, b.description, b.is_available, b.created_at, b.updated_at,
                       u.username
                FROM books b
                INNER JOIN users u ON b.user_id = u.id
                WHERE b.is_available = 1";
        
        $params = [];
        if ($excludeUserId) {
            $sql .= " AND b.user_id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        $query = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $query->bindValue($key, $value, PDO::PARAM_INT);
        }
        $query->execute();
        
        $books = [];
        while ($bookData = $query->fetch(PDO::FETCH_ASSOC)) {
            $book = new Book();
            $book->hydrate($bookData);
            // Ajouter les infos du propriétaire dans un tableau associatif
            $books[] = [
                'book' => $book,
                'owner' => [
                    'username' => $bookData['username']
                ]
            ];
        }
        
        return $books;
    }

    /**
     * Crée un nouveau livre
     */
    public function createBook($bookData)
    {
        $sql = "INSERT INTO books (user_id, title, author, image, description, is_available, created_at, updated_at)
                VALUES (:user_id, :title, :author, :image, :description, :is_available, NOW(), NOW())";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $bookData['user_id'], PDO::PARAM_INT);
        $query->bindValue(':title', $bookData['title']);
        $query->bindValue(':author', $bookData['author']);
        $query->bindValue(':image', $bookData['image'] ?? null);
        $query->bindValue(':description', $bookData['description']);
        $query->bindValue(':is_available', $bookData['is_available'] ?? 1, PDO::PARAM_INT);
        
        if ($query->execute()) {
            $bookId = $this->db->lastInsertId();
            return $this->findById($bookId);
        }
        
        return false;
    }

    /**
     * Met à jour un livre
     */
    public function updateBook($id, $bookData)
    {
        $sql = "UPDATE books 
                SET title = :title, 
                    author = :author, 
                    image = :image, 
                    description = :description, 
                    is_available = :is_available,
                    updated_at = NOW()
                WHERE id = :id";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':title', $bookData['title']);
        $query->bindValue(':author', $bookData['author']);
        $query->bindValue(':image', $bookData['image'] ?? null);
        $query->bindValue(':description', $bookData['description']);
        $query->bindValue(':is_available', $bookData['is_available'] ?? 1, PDO::PARAM_INT);
        
        if ($query->execute()) {
            return $this->findById($id);
        }
        
        return false;
    }

    /**
     * Supprime un livre
     */
    public function deleteBook($id)
    {
        $sql = "DELETE FROM books WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($query->execute()) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression du livre.'];
    }

    /**
     * Met à jour le statut de disponibilité d'un livre
     */
    public function updateAvailability($id, $isAvailable)
    {
        $sql = "UPDATE books 
                SET is_available = :is_available, updated_at = NOW()
                WHERE id = :id";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':is_available', $isAvailable ? 1 : 0, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /**
     * Recherche des livres par titre ou auteur
     */
    public function searchBooks($searchTerm, $excludeUserId = null)
    {
        $sql = "SELECT b.id, b.user_id, b.title, b.author, b.image, b.description, b.is_available, b.created_at, b.updated_at,
                       u.username
                FROM books b
                INNER JOIN users u ON b.user_id = u.id
                WHERE b.is_available = 1 
                  AND (b.title LIKE :search_term_title OR b.author LIKE :search_term_author)";
        
        $searchPattern = '%' . $searchTerm . '%';
        $params = [
            ':search_term_title' => $searchPattern,
            ':search_term_author' => $searchPattern
        ];
        
        if ($excludeUserId) {
            $sql .= " AND b.user_id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        $query = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':exclude_user_id') {
                $query->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $query->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $query->execute();
        
        $books = [];
        while ($bookData = $query->fetch(PDO::FETCH_ASSOC)) {
            $book = new Book();
            $book->hydrate($bookData);
            // Ajouter les infos du propriétaire dans un tableau associatif
            $books[] = [
                'book' => $book,
                'owner' => [
                    'username' => $bookData['username']
                ]
            ];
        }
        
        return $books;
    }

    /**
     * Compte le nombre de livres d'un utilisateur
     */
    public function countUserBooks($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM books WHERE user_id = :user_id";
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
        
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Compte le nombre de livres disponibles d'un utilisateur
     */
    public function countAvailableUserBooks($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM books WHERE user_id = :user_id AND is_available = 1";
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
        
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }
}