<?php

/**
 * MessageManager - Gestion des messages en base de données
 * 
 * Responsabilités :
 * - Gestion des conversations (liste et affichage)
 * - Envoi de messages
 * - Gestion du statut lu/non lu
 * - Comptage des messages non lus
 */
class MessageManager
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ================================
       LECTURE - CONVERSATIONS
       ================================ */

    /**
     * Récupère la liste des conversations pour un utilisateur
     * Une conversation = dernier message échangé avec chaque personne
     * 
     * @param int $userId ID de l'utilisateur
     * @return Message[] Tableau de conversations avec infos de l'autre utilisateur et compteur non lus
     */
    public function getConversations($userId) {
        $sql = "
            SELECT DISTINCT
                m.*,
                CASE 
                    WHEN m.sender_id = ? THEN recipient.username
                    ELSE sender.username
                END as other_username,
                CASE 
                    WHEN m.sender_id = ? THEN recipient.avatar
                    ELSE sender.avatar
                END as other_avatar,
                CASE 
                    WHEN m.sender_id = ? THEN m.recipient_id
                    ELSE m.sender_id
                END as other_user_id,
                (SELECT COUNT(*) 
                 FROM messages m2 
                 WHERE ((m2.sender_id = m.sender_id AND m2.recipient_id = m.recipient_id) 
                     OR (m2.sender_id = m.recipient_id AND m2.recipient_id = m.sender_id))
                   AND m2.recipient_id = ? 
                   AND m2.is_read = 0
                ) as unread_count
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users recipient ON m.recipient_id = recipient.id
            WHERE (m.sender_id = ? OR m.recipient_id = ?)
            AND m.id IN (
                SELECT MAX(m3.id)
                FROM messages m3
                WHERE (m3.sender_id = m.sender_id AND m3.recipient_id = m.recipient_id)
                   OR (m3.sender_id = m.recipient_id AND m3.recipient_id = m.sender_id)
                GROUP BY 
                    CASE 
                        WHEN m3.sender_id < m3.recipient_id 
                        THEN CONCAT(m3.sender_id, '-', m3.recipient_id)
                        ELSE CONCAT(m3.recipient_id, '-', m3.sender_id)
                    END
            )
            ORDER BY m.created_at DESC
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $userId, PDO::PARAM_INT);
        $statement->bindValue(2, $userId, PDO::PARAM_INT);
        $statement->bindValue(3, $userId, PDO::PARAM_INT);
        $statement->bindValue(4, $userId, PDO::PARAM_INT);
        $statement->bindValue(5, $userId, PDO::PARAM_INT);
        $statement->bindValue(6, $userId, PDO::PARAM_INT);
        $statement->execute();
        
        $conversations = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $conversation = new Message();
            $conversation->setId($row['id']);
            $conversation->setSenderId($row['sender_id']);
            $conversation->setRecipientId($row['recipient_id']);
            $conversation->setContent($row['content']);
            $conversation->setIsRead($row['is_read']);
            $conversation->setCreatedAt($row['created_at']);
            $conversation->setOtherUserId($row['other_user_id']);
            $conversation->setOtherUsername($row['other_username']);
            $conversation->setOtherAvatar($row['other_avatar']);
            $conversation->setUnreadCount($row['unread_count']);
            $conversations[] = $conversation;
        }
        
        return $conversations;
    }

    /**
     * Récupère tous les messages d'une conversation entre deux utilisateurs
     * 
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du deuxième utilisateur
     * @return Message[] Tableau de messages triés par date croissante
     */
    public function getConversationMessages($userId1, $userId2) {
        $sql = "
            SELECT m.*
            FROM messages m
            WHERE (m.sender_id = ? AND m.recipient_id = ?)
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.created_at ASC
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $userId1, PDO::PARAM_INT);
        $statement->bindValue(2, $userId2, PDO::PARAM_INT);
        $statement->bindValue(3, $userId2, PDO::PARAM_INT);
        $statement->bindValue(4, $userId1, PDO::PARAM_INT);
        $statement->execute();
        
        $messages = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message->setId($row['id']);
            $message->setSenderId($row['sender_id']);
            $message->setRecipientId($row['recipient_id']);
            $message->setContent($row['content']);
            $message->setIsRead($row['is_read']);
            $message->setCreatedAt($row['created_at']);
            $messages[] = $message;
        }
        
        return $messages;
    }

    /* ================================
       CRÉATION
       ================================ */

    /**
     * Envoie un nouveau message
     * 
     * @param int $senderId ID de l'expéditeur
     * @param int $recipientId ID du destinataire
     * @param string $content Contenu du message
     * @return int|false ID du message créé ou false en cas d'échec
     */
    public function sendMessage($senderId, $recipientId, $content) : int|false
    {
        $sql = "
            INSERT INTO messages (sender_id, recipient_id, content, is_read, created_at) 
            VALUES (:sender_id, :recipient_id, :content, 0, NOW())
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':sender_id', $senderId, PDO::PARAM_INT);
        $statement->bindValue(':recipient_id', $recipientId, PDO::PARAM_INT);
        $statement->bindValue(':content', $content, PDO::PARAM_STR);
        
        if ($statement->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /* ================================
       MODIFICATION - STATUT LU/NON LU
       ================================ */

    /**
     * Marque tous les messages d'une conversation comme lus
     * Utilisé quand l'utilisateur ouvre une conversation
     * 
     * @param int $currentUserId ID de l'utilisateur qui lit les messages
     * @param int $otherUserId ID de l'autre personne dans la conversation
     * @return bool True si succès
     */
    public function markConversationAsRead($currentUserId, $otherUserId) : bool
    {
        $sql = "
            UPDATE messages 
            SET is_read = 1 
            WHERE sender_id = :other_user_id 
              AND recipient_id = :current_user_id 
              AND is_read = 0
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
        $statement->bindValue(':other_user_id', $otherUserId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /* ================================
       STATISTIQUES
       ================================ */

    /**
     * Compte le nombre total de messages non lus pour un utilisateur
     * Utilisé pour afficher le badge de notifications
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de messages non lus
     */
    public function getUnreadCount($userId) {
        $sql = "
            SELECT COUNT(*) as count
            FROM messages 
            WHERE recipient_id = :user_id AND is_read = 0
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
