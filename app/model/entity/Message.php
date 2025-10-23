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
    }

    public function setSenderId($senderId) {
        $this->senderId = (int) $senderId;
    }

    public function setRecipientId($recipientId) {
        $this->recipientId = (int) $recipientId;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function setIsRead($isRead) {
        $this->isRead = (bool) $isRead;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setSenderUsername($senderUsername) {
        $this->senderUsername = $senderUsername;
    }

    public function setSenderAvatar($senderAvatar) {
        $this->senderAvatar = $senderAvatar;
    }

    public function setRecipientUsername($recipientUsername) {
        $this->recipientUsername = $recipientUsername;
    }

    public function setRecipientAvatar($recipientAvatar) {
        $this->recipientAvatar = $recipientAvatar;
    }
    
    public function setOtherUserId($otherUserId) {
        $this->otherUserId = (int) $otherUserId;
    }

    public function setOtherUsername($otherUsername) {
        $this->otherUsername = $otherUsername;
    }

    public function setOtherAvatar($otherAvatar) {
        $this->otherAvatar = $otherAvatar;
    }

    public function setUnreadCount($unreadCount) {
        $this->unreadCount = (int) $unreadCount;
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

    /**
     * Retourne l'ID de l'autre participant dans la conversation
     * (l'expéditeur si on est le destinataire, et vice versa)
     */
    public function getOtherParticipantId($currentUserId) {
        return $this->senderId == $currentUserId ? $this->recipientId : $this->senderId;
    }

    /**
     * Retourne le nom d'utilisateur de l'autre participant
     */
    public function getOtherParticipantUsername($currentUserId) {
        return $this->senderId == $currentUserId ? $this->recipientUsername : $this->senderUsername;
    }

    /**
     * Retourne l'avatar de l'autre participant
     */
    public function getOtherParticipantAvatar($currentUserId) {
        return $this->senderId == $currentUserId ? $this->recipientAvatar : $this->senderAvatar;
    }
}