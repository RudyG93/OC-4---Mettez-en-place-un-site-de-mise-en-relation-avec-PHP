<?php

/**
 * Manager pour la gestion des livres
 */
class BookManager
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les livres d'un utilisateur
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM books WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $books = [];
        foreach ($rows as $row) {
            $book = new Book();
            $book->setId($row['id']);
            $book->setUserId($row['user_id']);
            $book->setTitle($row['title']);
            $book->setAuthor($row['author']);
            $book->setImage($row['image']);
            $book->setDescription($row['description']);
            $book->setIsAvailable($row['is_available']);
            $book->setCreatedAt($row['created_at']);
            $books[] = $book;
        }
        
        return $books;
    }

    /**
     * Récupère un livre par son ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM books WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $bookData = $stmt->fetch();
        
        if (!$bookData) {
            return null;
        }
        
        $book = new Book();
        $book->setId($bookData['id']);
        $book->setUserId($bookData['user_id']);
        $book->setTitle($bookData['title']);
        $book->setAuthor($bookData['author']);
        $book->setImage($bookData['image']);
        $book->setDescription($bookData['description']);
        $book->setIsAvailable($bookData['is_available']);
        $book->setCreatedAt($bookData['created_at']);
        
        return $book;
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
            $book->setId($bookData['id']);
            $book->setUserId($bookData['user_id']);
            $book->setTitle($bookData['title']);
            $book->setAuthor($bookData['author']);
            $book->setImage($bookData['image']);
            $book->setDescription($bookData['description']);
            $book->setIsAvailable($bookData['is_available']);
            $book->setCreatedAt($bookData['created_at']);
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
     * Récupère les derniers livres ajoutés
     */
    public function getLatestBooks($limit = 4)
    {
        $sql = "SELECT b.id, b.user_id, b.title, b.author, b.image, b.description, b.is_available, b.created_at, b.updated_at,
                       u.username
                FROM books b
                INNER JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC
                LIMIT :limit";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        
        $books = [];
        while ($bookData = $query->fetch(PDO::FETCH_ASSOC)) {
            $book = new Book();
            $book->setId($bookData['id']);
            $book->setUserId($bookData['user_id']);
            $book->setTitle($bookData['title']);
            $book->setAuthor($bookData['author']);
            $book->setImage($bookData['image']);
            $book->setDescription($bookData['description']);
            $book->setIsAvailable($bookData['is_available']);
            $book->setCreatedAt($bookData['created_at']);
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
            $book->setId($bookData['id']);
            $book->setUserId($bookData['user_id']);
            $book->setTitle($bookData['title']);
            $book->setAuthor($bookData['author']);
            $book->setImage($bookData['image']);
            $book->setDescription($bookData['description']);
            $book->setIsAvailable($bookData['is_available']);
            $book->setCreatedAt($bookData['created_at']);
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
        $sql = "SELECT COUNT(*) as total FROM books WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Compte le nombre de livres disponibles d'un utilisateur
     */
    public function countAvailableUserBooks($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM books WHERE user_id = :user_id AND is_available = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }
}