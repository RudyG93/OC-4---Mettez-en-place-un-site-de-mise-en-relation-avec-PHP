<?php

/**
 * BookManager - Gestion des livres en base de données
 * 
 * Responsabilités :
 * - CRUD complet des livres
 * - Recherche et filtrage (disponibilité, propriétaire, recherche textuelle)
 * - Statistiques (comptage par utilisateur)
 */
class BookManager
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
     * Récupère tous les livres d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return Book[] Tableau de livres triés par date de création (récent en premier)
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
     * 
     * @param int $id ID du livre
     * @return Book|null Le livre ou null si introuvable
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
     * Récupère tous les livres disponibles pour l'échange
     * 
     * @param int|null $excludeUserId ID de l'utilisateur à exclure (généralement l'utilisateur connecté)
     * @return array Tableau associatif avec 'book' et 'owner' (username)
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
            
            $books[] = [
                'book' => $book,
                'owner' => ['username' => $bookData['username']]
            ];
        }
        
        return $books;
    }

    /**
     * Récupère les derniers livres ajoutés sur la plateforme
     * 
     * @param int $limit Nombre maximum de livres à récupérer
     * @return array Tableau associatif avec 'book' et 'owner' (username)
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
                'owner' => ['username' => $bookData['username']]
            ];
        }
        
        return $books;
    }

    /**
     * Recherche des livres disponibles par titre ou auteur
     * 
     * @param string $searchTerm Terme de recherche
     * @param int|null $excludeUserId ID de l'utilisateur à exclure
     * @return array Tableau associatif avec 'book' et 'owner' (username)
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
            
            $books[] = [
                'book' => $book,
                'owner' => ['username' => $bookData['username']]
            ];
        }
        
        return $books;
    }

    /* ================================
       CRÉATION
       ================================ */

    /**
     * Crée un nouveau livre en base de données
     * 
     * @param array $bookData Données du livre (user_id, title, author, image, description, is_available)
     * @return Book|false Le livre créé ou false en cas d'échec
     */
    public function createBook($bookData)
    {
        $sql = "INSERT INTO books (user_id, title, author, image, description, is_available, created_at)
                VALUES (:user_id, :title, :author, :image, :description, :is_available, NOW())";
        
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

    /* ================================
       MODIFICATION
       ================================ */

    /**
     * Met à jour un livre existant
     * 
     * @param int $id ID du livre à modifier
     * @param array $bookData Nouvelles données du livre
     * @return Book|false Le livre mis à jour ou false en cas d'échec
     */
    public function updateBook($id, $bookData)
    {
        $sql = "UPDATE books 
                SET title = :title, 
                    author = :author, 
                    image = :image, 
                    description = :description, 
                    is_available = :is_available
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
     * Met à jour uniquement le statut de disponibilité d'un livre
     * 
     * @param int $id ID du livre
     * @param bool $isAvailable Nouveau statut de disponibilité
     * @return bool True si succès
     */
    public function updateAvailability($id, $isAvailable)
    {
        $sql = "UPDATE books 
                SET is_available = :is_available
                WHERE id = :id";
        
        $query = $this->db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':is_available', $isAvailable ? 1 : 0, PDO::PARAM_INT);
        
        return $query->execute();
    }

    /* ================================
       SUPPRESSION
       ================================ */

    /**
     * Supprime un livre de la base de données
     * 
     * @param int $id ID du livre à supprimer
     * @return array Résultat avec 'success' et éventuellement 'error'
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

    /* ================================
       STATISTIQUES
       ================================ */

    /**
     * Compte le nombre total de livres d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de livres
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
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de livres disponibles
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