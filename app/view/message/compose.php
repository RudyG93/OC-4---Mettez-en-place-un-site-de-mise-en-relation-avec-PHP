<?php
// Récupérer le message flash s'il existe
$flash = Session::getFlash();
$activePage = 'messagerie';
?>

<div class="compose-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif?>

    <div class="compose-content">
        <!-- En-tête -->
        <div class="compose-header">
            <a href="<?= BASE_URL ?>messages" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour aux messages
            </a>
            <h1 class="compose-title">Nouveau message</h1>
        </div>

        <!-- Destinataire -->
        <div class="recipient-info">
            <div class="recipient-avatar">
                <?php if ($recipient->getAvatar()): ?>
                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $recipient->getAvatar() ?>" 
                         alt="<?= e($recipient->getUsername()) ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($recipient->getUsername(), 0, 1)) ?>
                    </div>
                <?php endif?>
            </div>
            <div class="recipient-details">
                <h2 class="recipient-name">À : <?= e($recipient->getUsername()) ?></h2>
                <a href="<?= BASE_URL ?>profil/<?= $recipient->getId() ?>" class="profile-link">
                    Voir le profil
                </a>
                
                <?php if (isset($_GET['book_title'])): ?>
                    <div class="message-context">
                        <i class="fas fa-book"></i>
                        <span>À propos du livre : <strong><?= e($_GET['book_title']) ?></strong></span>
                    </div>
                <?php endif?>
            </div>
        </div>

        <!-- Formulaire de composition -->
        <form id="composeForm" class="compose-form" method="POST" action="<?= BASE_URL ?>messages/send">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="recipient_id" value="<?= $recipient->getId() ?>">
            
            <div class="form-group">
                <label for="messageContent" class="form-label">Votre message</label>
                <textarea 
                    name="content" 
                    id="messageContent" 
                    class="form-textarea"
                    placeholder="Tapez votre message ici..."
                    rows="8"
                    maxlength="1000"
                    required></textarea>
                <div class="form-meta">
                    <small class="form-help">Maximum 1000 caractères. Présentez-vous et expliquez pourquoi vous souhaitez entrer en contact.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer le message
                </button>
                <a href="<?= BASE_URL ?>messages" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* Styles pour la composition de message */
.compose-container {
    max-width: 700px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.compose-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.compose-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    background-color: #f8f9fa;
}

.back-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.back-link:hover {
    color: var(--primary-hover);
}

.compose-title {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.recipient-info {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.recipient-avatar img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
}

.recipient-name {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
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

.message-context {
    margin-top: 0.75rem;
    padding: 0.75rem;
    background-color: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.message-context i {
    color: var(--primary-color);
}

.message-context strong {
    color: #333;
}

.compose-form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-textarea {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1rem;
    font-family: inherit;
    font-size: 0.9rem;
    line-height: 1.5;
    resize: vertical;
    min-height: 150px;
}

.form-textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
}

.form-meta {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-top: 0.5rem;
    gap: 1rem;
}

.char-counter {
    font-size: 0.8rem;
    color: #666;
    white-space: nowrap;
}

.form-help {
    font-size: 0.8rem;
    color: #666;
    font-style: italic;
}

.form-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
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

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
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
    .compose-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
    
    .compose-header,
    .recipient-info,
    .compose-form {
        padding: 1rem;
    }
    
    .recipient-avatar img,
    .avatar-placeholder {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .compose-title {
        font-size: 1.5rem;
    }
    
    .recipient-name {
        font-size: 1.1rem;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>