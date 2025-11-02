<?php

class Message extends Entity {
    protected $senderId;
    protected $recipientId;
    protected $content;
    protected $isRead;
    protected $createdAt;
    
    // Propriétés pour les informations de l'expéditeur et destinataire (jointures)
    protected $senderUsername = null;
    protected $senderAvatar = null;
    protected $recipientUsername = null;
    protected $recipientAvatar = null;
    
    // Propriétés pour les conversations
    protected $otherUserId = null;
    protected $otherUsername = null;
    protected $otherAvatar = null;
    protected $unreadCount = 0;

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getSenderId() {
        return $this->senderId;
    }

    public function getRecipientId() {
        return $this->recipientId;
    }

    public function getContent() {
        return $this->content;
    }

    public function isRead() {
        return $this->isRead;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getSenderUsername() {
        return $this->senderUsername;
    }

    public function getSenderAvatar() {
        return $this->senderAvatar;
    }

    public function getRecipientUsername() {
        return $this->recipientUsername;
    }

    public function getRecipientAvatar() {
        return $this->recipientAvatar;
    }
    
    public function getOtherUserId() {
        return $this->otherUserId;
    }

    public function getOtherUsername() {
        return $this->otherUsername;
    }

    public function getOtherAvatar() {
        return $this->otherAvatar;
    }

    public function getUnreadCount() {
        return $this->unreadCount;
    }

    // Setters
    public function setId($id) {
        $this->id = (int) $id;
        return $this;
    }

    public function setSenderId($senderId) {
        $this->senderId = (int) $senderId;
        return $this;
    }

    public function setRecipientId($recipientId) {
        $this->recipientId = (int) $recipientId;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function setIsRead($isRead) {
        $this->isRead = (bool) $isRead;
        return $this;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setSenderUsername($senderUsername) {
        $this->senderUsername = $senderUsername;
        return $this;
    }

    public function setSenderAvatar($senderAvatar) {
        $this->senderAvatar = $senderAvatar;
        return $this;
    }

    public function setRecipientUsername($recipientUsername) {
        $this->recipientUsername = $recipientUsername;
        return $this;
    }

    public function setRecipientAvatar($recipientAvatar) {
        $this->recipientAvatar = $recipientAvatar;
        return $this;
    }
    
    public function setOtherUserId($otherUserId) {
        $this->otherUserId = (int) $otherUserId;
        return $this;
    }

    public function setOtherUsername($otherUsername) {
        $this->otherUsername = $otherUsername;
        return $this;
    }

    public function setOtherAvatar($otherAvatar) {
        $this->otherAvatar = $otherAvatar;
        return $this;
    }

    public function setUnreadCount($unreadCount) {
        $this->unreadCount = (int) $unreadCount;
        return $this;
    }

    /**
     * Méthodes utilitaires
     */

    /**
     * Vérifie si le message a été envoyé aujourd'hui
     */
    public function isToday() {
        return date('Y-m-d') === date('Y-m-d', strtotime($this->createdAt));
    }

    /**
     * Retourne une version formatée de la date de création
     */
    public function getFormattedDate() {
        $timestamp = strtotime($this->createdAt);
        
        if ($this->isToday()) {
            return date('H:i', $timestamp);
        } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('-1 day'))) {
            return 'Hier ' . date('H:i', $timestamp);
        } else {
            return date('d/m/Y H:i', $timestamp);
        }
    }

    /**
     * Retourne un extrait du contenu pour les listes de conversations
     */
    public function getExcerpt($maxLength = 50) {
        if (strlen($this->content) <= $maxLength) {
            return $this->content;
        }
        
        return substr($this->content, 0, $maxLength) . '...';
    }

    /**
     * Vérifie si l'utilisateur donné est l'expéditeur de ce message
     */
    public function isSentBy($userId) {
        return $this->senderId == $userId;
    }

    /**
     * Vérifie si l'utilisateur donné est le destinataire de ce message
     */
    public function isReceivedBy($userId) {
        return $this->recipientId == $userId;
    }
}
