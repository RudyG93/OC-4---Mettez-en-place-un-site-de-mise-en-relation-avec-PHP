<?php $activePage = 'messagerie'; ?>

<div class="messagerie-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- COLONNE DROITE: Conversation active -->
    <div class="conversation-main">
        <!-- En-tête de la conversation -->
        <div class="conversation-header">
            <div class="conversation-header-content">
                <div class="header-avatar">
                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $otherUser->getAvatar() ?>" 
                         alt="<?= e($otherUser->getUsername()) ?>">
                </div>
                <div class="header-info">
                    <h2 class="header-username"><?= e($otherUser->getUsername()) ?></h2>
                    <a href="<?= BASE_URL ?>profil/<?= $otherUser->getId() ?>" class="header-link">
                        Voir le profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Zone des messages -->
        <div class="messages-zone">
            <div class="messages-list" id="messagesList">
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        <p><strong>Aucun message dans cette conversation</strong></p>
                        <p>Commencez la discussion !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item <?= $message->isSentBy($currentUser->getId()) ? 'sent' : 'received' ?>">
                            <?php if ($message->isSentBy($currentUser->getId())): ?>
                                <!-- Messages envoyés : meta puis bulle -->
                                <div class="message-content">
                                    <div class="message-meta">
                                        <span class="message-time"><?= $message->getFormattedDate() ?></span>
                                        <?php if ($message->isRead()): ?>
                                            <span class="message-status">Lu</span>
                                        <?php endif?>
                                    </div>
                                    <div class="message-bubble">
                                        <p class="message-text"><?= nl2br(e($message->getContent())) ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Messages reçus : header (avatar + meta) puis bulle -->
                                <div class="message-header">
                                    <div class="message-avatar">
                                        <img src="<?= BASE_URL ?>uploads/avatars/<?= $otherUser->getAvatar() ?>" 
                                             alt="<?= e($otherUser->getUsername()) ?>">
                                    </div>
                                    <div class="message-meta">
                                        <span class="message-time"><?= $message->getFormattedDate() ?></span>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <p class="message-text"><?= nl2br(e($message->getContent())) ?></p>
                                    </div>
                                </div>
                            <?php endif?>
                        </div>
                    <?php endforeach?>
                <?php endif?>
            </div>
        </div>

        <!-- Formulaire d'envoi -->
        <div class="message-form-container">
            <form id="messageForm" class="message-form" method="POST" action="<?= BASE_URL ?>messages/send">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="recipient_id" value="<?= $otherUser->getId() ?>">
                
                <div class="form-input-wrapper">
                    <input 
                        type="text"
                        name="content" 
                        id="messageContent" 
                        class="message-input"
                        placeholder="Tapez votre message ici" 
                        maxlength="1000"
                        required>
                </div>
                <button type="submit" class="btn-send">Envoyer</button>
            </form>
        </div>
    </div>
</div>