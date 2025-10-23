<?php

class MessageManager extends Model {

    /**
     * Récupère la liste des conversations pour un utilisateur
     * Une conversation = dernier message échangé avec chaque personne
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
            $conversation->hydrate($row);
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
     */
    public function getConversationMessages($userId1, $userId2) {
        $sql = "
            SELECT 
                m.*,
                sender.username as sender_username,
                sender.avatar as sender_avatar,
                recipient.username as recipient_username,
                recipient.avatar as recipient_avatar
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users recipient ON m.recipient_id = recipient.id
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
            $message->hydrate($row);
            $messages[] = $message;
        }
        
        return $messages;
    }

    /**
     * Envoie un nouveau message
     */
    public function sendMessage($senderId, $recipientId, $content) {
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

    /**
     * Marque tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead($currentUserId, $otherUserId) {
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

    /**
     * Marque un message spécifique comme lu
     */
    public function markAsRead($messageId, $userId) {
        $sql = "
            UPDATE messages 
            SET is_read = 1 
            WHERE id = :message_id 
              AND recipient_id = :user_id
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':message_id', $messageId, PDO::PARAM_INT);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Compte le nombre total de messages non lus pour un utilisateur
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

    /**
     * Vérifie si deux utilisateurs ont déjà échangé des messages
     */
    public function hasConversation($userId1, $userId2) {
        $sql = "
            SELECT COUNT(*) as count
            FROM messages 
            WHERE (sender_id = ? AND recipient_id = ?)
               OR (sender_id = ? AND recipient_id = ?)
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $userId1, PDO::PARAM_INT);
        $statement->bindValue(2, $userId2, PDO::PARAM_INT);
        $statement->bindValue(3, $userId2, PDO::PARAM_INT);
        $statement->bindValue(4, $userId1, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Récupère le dernier message d'une conversation
     */
    public function getLastMessage($userId1, $userId2) {
        $sql = "
            SELECT 
                m.*,
                sender.username as sender_username,
                sender.avatar as sender_avatar,
                recipient.username as recipient_username,
                recipient.avatar as recipient_avatar
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users recipient ON m.recipient_id = recipient.id
            WHERE (m.sender_id = ? AND m.recipient_id = ?)
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.created_at DESC
            LIMIT 1
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $userId1, PDO::PARAM_INT);
        $statement->bindValue(2, $userId2, PDO::PARAM_INT);
        $statement->bindValue(3, $userId2, PDO::PARAM_INT);
        $statement->bindValue(4, $userId1, PDO::PARAM_INT);
        $statement->execute();
        
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $message = new Message();
            $message->hydrate($row);
            return $message;
        }
        
        return null;
    }

    /**
     * Supprime un message (optionnel - pour les fonctionnalités avancées)
     */
    public function deleteMessage($messageId, $userId) {
        // Vérifier que l'utilisateur est bien l'expéditeur
        $sql = "
            DELETE FROM messages 
            WHERE id = :message_id 
              AND sender_id = :user_id
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':message_id', $messageId, PDO::PARAM_INT);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Recherche dans les messages (fonctionnalité avancée)
     */
    public function searchMessages($userId, $query) {
        $sql = "
            SELECT 
                m.*,
                sender.username as sender_username,
                sender.avatar as sender_avatar,
                recipient.username as recipient_username,
                recipient.avatar as recipient_avatar
            FROM messages m
            INNER JOIN users sender ON m.sender_id = sender.id
            INNER JOIN users recipient ON m.recipient_id = recipient.id
            WHERE (m.sender_id = ? OR m.recipient_id = ?)
              AND m.content LIKE ?
            ORDER BY m.created_at DESC
            LIMIT 50
        ";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(1, $userId, PDO::PARAM_INT);
        $statement->bindValue(2, $userId, PDO::PARAM_INT);
        $statement->bindValue(3, '%' . $query . '%', PDO::PARAM_STR);
        $statement->execute();
        
        $messages = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $message = new Message();
            $message->hydrate($row);
            $messages[] = $message;
        }
        
        return $messages;
    }
}