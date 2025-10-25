<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$activePage = 'messagerie';
?>

<div class="conversation-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <!-- En-tête de la conversation -->
    <div class="conversation-header">
        <div class="header-left">
            <a href="<?= BASE_URL ?>messages" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour aux messages
            </a>
            <div class="conversation-info">
                <div class="conversation-avatar">
                    <?php if ($otherUser->getAvatar()): ?>
                        <img src="<?= BASE_URL ?>uploads/avatars/<?= $otherUser->getAvatar() ?>" 
                             alt="<?= e($otherUser->getUsername()) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($otherUser->getUsername(), 0, 1)) ?>
                        </div>
                    <?php endif?>
                </div>
                <div class="conversation-details">
                    <h1 class="conversation-title"><?= e($otherUser->getUsername()) ?></h1>
                    <a href="<?= BASE_URL ?>profil/<?= $otherUser->getId() ?>" class="profile-link">
                        Voir le profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des messages -->
    <div class="messages-container">
        <div class="messages-list" id="messagesList">
            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <p>Aucun message dans cette conversation pour l'instant.</p>
                    <p>Commencez la discussion !</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-item <?= $message->isSentBy($currentUser->getId()) ? 'sent' : 'received' ?>">
                        <div class="message-avatar">
                            <?php if ($message->isSentBy($currentUser->getId())): ?>
                                <!-- Avatar de l'utilisateur actuel ou placeholder -->
                                <div class="avatar-placeholder small">
                                    <?= strtoupper(substr($currentUser->getUsername(), 0, 1)) ?>
                                </div>
                            <?php else: ?>
                                <?php if ($otherUser->getAvatar()): ?>
                                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $otherUser->getAvatar() ?>" 
                                         alt="<?= e($otherUser->getUsername()) ?>" class="small">
                                <?php else: ?>
                                    <div class="avatar-placeholder small">
                                        <?= strtoupper(substr($otherUser->getUsername(), 0, 1)) ?>
                                    </div>
                                <?php endif?>
                            <?php endif?>
                        </div>
                        
                        <div class="message-content">
                            <div class="message-bubble">
                                <p class="message-text"><?= nl2br(e($message->getContent())) ?></p>
                                <div class="message-meta">
                                    <span class="message-time"><?= $message->getFormattedDate() ?></span>
                                    <?php if ($message->isSentBy($currentUser->getId()) && $message->isRead()): ?>
                                        <span class="message-status">Lu</span>
                                    <?php endif?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach?>
            <?php endif?>
        </div>
    </div>

    <!-- Formulaire d'envoi de message -->
    <div class="message-form-container">
        <form id="messageForm" class="message-form">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="recipient_id" value="<?= $otherUser->getId() ?>">
            
            <div class="form-input-container">
                <textarea 
                    name="content" 
                    id="messageContent" 
                    placeholder="Tapez votre message..." 
                    rows="2"
                    maxlength="1000"
                    required></textarea>
                <div class="form-actions">
                    <span class="char-counter">0/1000</span>
                    <button type="submit" class="btn btn-primary" disabled>
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Styles pour la conversation */
.conversation-container {
    max-width: 800px;
    margin: 1rem auto;
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.conversation-header {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    background-color: #f8f9fa;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.back-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.back-link:hover {
    color: var(--primary-hover);
}

.conversation-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.conversation-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.avatar-placeholder.small {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
}

.conversation-avatar img.small {
    width: 32px;
    height: 32px;
}

.conversation-title {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
}

.profile-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
}

.profile-link:hover {
    text-decoration: underline;
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.no-messages {
    text-align: center;
    color: #666;
    padding: 2rem;
}

.message-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.message-item.sent {
    flex-direction: row-reverse;
}

.message-item.sent .message-content {
    align-items: flex-end;
}

.message-content {
    display: flex;
    flex-direction: column;
    max-width: 70%;
}

.message-bubble {
    background-color: #f1f3f4;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    position: relative;
}

.message-item.sent .message-bubble {
    background-color: var(--primary-color);
    color: white;
}

.message-text {
    margin: 0;
    line-height: 1.4;
    word-wrap: break-word;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    opacity: 0.7;
}

.message-time {
    font-size: 0.75rem;
}

.message-status {
    font-size: 0.75rem;
}

.message-form-container {
    padding: 1rem;
    border-top: 1px solid #eee;
    background-color: #f8f9fa;
}

.message-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-input-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

#messageContent {
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 0.75rem 1rem;
    resize: none;
    font-family: inherit;
    font-size: 0.9rem;
    max-height: 120px;
    overflow-y: auto;
}

#messageContent:focus {
    outline: none;
    border-color: var(--primary-color);
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.char-counter {
    font-size: 0.8rem;
    color: #666;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: var(--primary-hover);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error,
.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Responsive */
@media (max-width: 768px) {
    .conversation-container {
        margin: 0;
        height: 100vh;
        border-radius: 0;
    }
    
    .conversation-header {
        padding: 0.75rem;
    }
    
    .conversation-info {
        gap: 0.75rem;
    }
    
    .conversation-avatar img,
    .avatar-placeholder {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .conversation-title {
        font-size: 1.1rem;
    }
    
    .messages-container {
        padding: 0.75rem;
    }
    
    .message-content {
        max-width: 85%;
    }
    
    .message-form-container {
        padding: 0.75rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const messageContent = document.getElementById('messageContent');
    const charCounter = document.querySelector('.char-counter');
    const submitButton = document.querySelector('button[type="submit"]');
    const messagesList = document.getElementById('messagesList');

    // Faire défiler vers le bas au chargement
    messagesList.scrollTop = messagesList.scrollHeight;

    // Gestion du compteur de caractères
    messageContent.addEventListener('input', function() {
        const length = this.value.length;
        charCounter.textContent = `${length}/1000`;
        
        // Activer/désactiver le bouton d'envoi
        submitButton.disabled = length === 0 || length > 1000;
        
        // Ajuster la hauteur du textarea
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Gestion de l'envoi du message
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Désactiver le bouton pendant l'envoi
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        
        fetch('<?= BASE_URL ?>messages/send', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page pour afficher le nouveau message
                window.location.reload();
            } else {
                alert('Erreur: ' + data.message);
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'envoi du message.');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
        });
    });

    // Envoi avec Ctrl+Enter
    messageContent.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            if (!submitButton.disabled) {
                messageForm.dispatchEvent(new Event('submit'));
            }
        }
    });
});
</script>