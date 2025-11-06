<?php $activePage = 'messagerie'; ?>

<div class="messagerie-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- COLONNE DROITE: Conversation active -->
    <div class="conversation-main">
        <!-- En-tête de la conversation -->
        <div class="conversation-header">
            <div class="conversation-header-content">
                <div class="header-avatar">
                    <img src="<?= BASE_URL ?>uploads/avatars/<?= escape($otherUser->getAvatar()) ?>" 
                         alt="<?= escape($otherUser->getUsername()) ?>">
                </div>
                <div class="header-info">
                    <h2 class="header-username"><?= escape($otherUser->getUsername()) ?></h2>
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
                        <div class="message-item <?= $message->getSenderId() == $userId ? 'sent' : 'received' ?>">
                            <?php if ($message->getSenderId() == $userId): ?>
                                <!-- Messages envoyés : meta puis bulle -->
                                <div class="message-content">
                                    <div class="message-meta">
                                        <span class="message-time">
                                            <?php 
                                            $timestamp = strtotime($message->getCreatedAt());
                                            $messageDate = date('Y-m-d', $timestamp);
                                            
                                            if ($messageDate === date('Y-m-d')) {
                                                echo date('H:i', $timestamp);
                                            } elseif ($messageDate === date('Y-m-d', strtotime('-1 day'))) {
                                                echo 'Hier ' . date('H:i', $timestamp);
                                            } else {
                                                echo date('d/m/Y H:i', $timestamp);
                                            }
                                            ?>
                                        </span>
                                        <?php if ($message->isRead()): ?>
                                            <span class="message-status">Lu</span>
                                        <?php endif?>
                                    </div>
                                    <div class="message-bubble">
                                        <p class="message-text"><?= nl2br(escape($message->getContent())) ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Messages reçus : header (avatar + meta) puis bulle -->
                                <div class="message-header">
                                    <div class="message-avatar">
                                        <img src="<?= BASE_URL ?>uploads/avatars/<?= escape($otherUser->getAvatar()) ?>" 
                                             alt="<?= escape($otherUser->getUsername()) ?>">
                                    </div>
                                    <div class="message-meta">
                                        <span class="message-time">
                                            <?php 
                                            $timestamp = strtotime($message->getCreatedAt());
                                            $messageDate = date('Y-m-d', $timestamp);
                                            
                                            if ($messageDate === date('Y-m-d')) {
                                                echo escape(date('H:i', $timestamp));
                                            } elseif ($messageDate === date('Y-m-d', strtotime('-1 day'))) {
                                                echo escape('Hier ' . date('H:i', $timestamp));
                                            } else {
                                                echo escape(date('d/m/Y H:i', $timestamp));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <p class="message-text"><?= nl2br(escape($message->getContent())) ?></p>
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
            <form id="messageForm" class="message-form" method="POST" action="<?= BASE_URL ?>messagerie/send">
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