<?php
/**
 * Classe Model (Manager) - Classe de base pour les managers
 * Gère les opérations CRUD sur la base de données
 */

abstract class Model
{
    protected $db;
    protected $table;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupère tous les enregistrements
     */
    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un enregistrement par ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Récupère des enregistrements avec condition WHERE
     */
    public function findBy(array $conditions, $orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = :$field";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($conditions as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un seul enregistrement avec condition WHERE
     */
    public function findOneBy(array $conditions)
    {
        $results = $this->findBy($conditions, null, 1);
        return $results ? $results[0] : null;
    }
    
    /**
     * Insère un nouvel enregistrement
     */
    public function create(array $data)
    {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Met à jour un enregistrement
     */
    public function update($id, array $data)
    {
        $setClause = [];
        foreach ($data as $field => $value) {
            $setClause[] = "$field = :$field";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Supprime un enregistrement
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Compte le nombre d'enregistrements
     */
    public function count(array $conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = :$field";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($conditions as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }
    
    /**
     * Hydrate une entité à partir de données
     * Méthode helper pour réduire la duplication dans les managers
     */
    protected function hydrateEntity($entityClass, $data)
    {
        if (!$data) {
            return null;
        }
        
        $entity = new $entityClass();
        $entity->hydrate($data);
        return $entity;
    }
    
    /**
     * Hydrate plusieurs entités à partir d'un tableau de données
     */
    protected function hydrateEntities($entityClass, array $dataArray)
    {
        $entities = [];
        foreach ($dataArray as $data) {
            $entities[] = $this->hydrateEntity($entityClass, $data);
        }
        return $entities;
    }
}
