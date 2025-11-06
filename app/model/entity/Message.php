<?php

/**
 * Entité Message - Représente un message entre utilisateurs
 * 
 * Cette entité gère deux contextes d'utilisation :
 * 1. Messages individuels : id, sender, recipient, content, is_read, created_at
 * 2. Conversations (dernier message) : + other_user_id, other_username, other_avatar, unread_count
 * 
 * Les propriétés "other_*" et "unread_count" sont remplies uniquement dans le contexte
 * de la liste des conversations (méthode getConversations).
 */
class Message
{
    /* ================================
       PROPRIÉTÉS - MESSAGE DE BASE
       ================================ */

    protected $id;
    protected $senderId;
    protected $recipientId;
    protected $content;
    protected $isRead;
    protected $createdAt;

    /* ================================
       PROPRIÉTÉS - CONVERSATION
       (optionnelles, remplies uniquement dans getConversations)
       ================================ */

    // Informations pour la liste des conversations
    protected $otherUserId = null;      // ID de l'autre personne dans la conversation
    protected $otherUsername = null;     // Pseudo de l'autre personne
    protected $otherAvatar = null;       // Avatar de l'autre personne
    protected $unreadCount = 0;          // Nombre de messages non lus dans cette conversation

    /* ================================
       GETTERS - MESSAGE DE BASE
       ================================ */
    public function getId()
    {
        return $this->id;
    }

    public function getSenderId()
    {
        return $this->senderId;
    }

    public function getRecipientId()
    {
        return $this->recipientId;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isRead()
    {
        return $this->isRead;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /* ================================
       GETTERS - CONVERSATION
       ================================ */

    public function getOtherUserId()
    {
        return $this->otherUserId;
    }

    public function getOtherUsername()
    {
        return $this->otherUsername;
    }

    public function getOtherAvatar()
    {
        return $this->otherAvatar;
    }

    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /* ================================
       SETTERS - MESSAGE DE BASE
       ================================ */

    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    public function setSenderId($senderId)
    {
        $this->senderId = (int) $senderId;
        return $this;
    }

    public function setRecipientId($recipientId)
    {
        $this->recipientId = (int) $recipientId;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setIsRead($isRead)
    {
        $this->isRead = (bool) $isRead;
        return $this;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /* ================================
       SETTERS - CONVERSATION
       ================================ */

    public function setOtherUserId($otherUserId)
    {
        $this->otherUserId = (int) $otherUserId;
        return $this;
    }

    public function setOtherUsername($otherUsername)
    {
        $this->otherUsername = $otherUsername;
        return $this;
    }

    public function setOtherAvatar($otherAvatar)
    {
        $this->otherAvatar = $otherAvatar;
        return $this;
    }

    public function setUnreadCount($unreadCount)
    {
        $this->unreadCount = (int) $unreadCount;
        return $this;
    }
}
