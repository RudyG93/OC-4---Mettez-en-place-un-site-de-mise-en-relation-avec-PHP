# 💬 06 - SYSTÈME DE MESSAGERIE

## Vue d'ensemble

Le système de messagerie permet aux utilisateurs de :
- ✅ Communiquer entre eux
- ✅ Discuter à propos des livres
- ✅ Gérer leurs conversations
- ✅ Envoyer des messages en temps réel (AJAX)

---

## Architecture

### Composants

```
MessageController
    ├── index() - Liste des conversations
    ├── conversation($userId) - Fil de discussion
    ├── compose($recipientId) - Nouveau message
    └── send() - Envoi AJAX

MessageManager
    ├── getConversations($userId)
    ├── getConversationMessages($user1, $user2)
    ├── sendMessage($sender, $recipient, $content)
    ├── markConversationAsRead($userId, $otherUserId)
    └── getUnreadCount($userId)

Message (Entity)
    ├── sender_id, recipient_id, content
    ├── is_read, created_at
    └── Propriétés jointures (username, avatar)
```

---

## Liste des conversations

### Route : `/messages`

**Fonctionnement** :
```php
public function index() {
    requireAuth();
    
    $userId = Session::get('user_id');
    $conversations = $this->messageManager->getConversations($userId);
    
    $this->render('message/index', [
        'conversations' => $conversations
    ]);
}
```

**Requête SQL** :
```php
public function getConversations($userId) {
    $sql = "SELECT 
                CASE 
                    WHEN m.sender_id = ? THEN m.recipient_id
                    ELSE m.sender_id
                END as other_user_id,
                u.username,
                u.avatar,
                MAX(m.created_at) as last_message_time,
                (
                    SELECT content 
                    FROM messages 
                    WHERE (sender_id = ? AND recipient_id = other_user_id)
                       OR (sender_id = other_user_id AND recipient_id = ?)
                    ORDER BY created_at DESC 
                    LIMIT 1
                ) as last_message,
                (
                    SELECT COUNT(*) 
                    FROM messages 
                    WHERE recipient_id = ? 
                      AND sender_id = other_user_id
                      AND is_read = 0
                ) as unread_count
            FROM messages m
            JOIN users u ON u.id = CASE 
                WHEN m.sender_id = ? THEN m.recipient_id
                ELSE m.sender_id
            END
            WHERE m.sender_id = ? OR m.recipient_id = ?
            GROUP BY other_user_id, u.username, u.avatar
            ORDER BY last_message_time DESC";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Affichage** :
```php
<div class="conversations-list">
    <?php if (empty($conversations)): ?>
        <p class="empty">Aucune conversation</p>
    <?php else: ?>
        <?php foreach ($conversations as $conv): ?>
            <a href="<?= url('messages/conversation/' . $conv['other_user_id']) ?>" 
               class="conversation-item <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>">
                
                <div class="avatar">
                    <?php if ($conv['avatar']): ?>
                        <img src="<?= url('uploads/' . h($conv['avatar'])) ?>" 
                             alt="<?= h($conv['username']) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($conv['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="conversation-info">
                    <div class="header">
                        <span class="username"><?= h($conv['username']) ?></span>
                        <span class="time"><?= formatDate($conv['last_message_time']) ?></span>
                    </div>
                    <p class="last-message"><?= h($conv['last_message']) ?></p>
                </div>
                
                <?php if ($conv['unread_count'] > 0): ?>
                    <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
```

---

## Fil de discussion

### Route : `/messages/conversation/{userId}`

**Fonctionnement** :
```php
public function conversation($otherUserId) {
    requireAuth();
    
    $currentUserId = Session::get('user_id');
    
    // Récupérer l'autre utilisateur
    $otherUser = $this->userManager->getUserById($otherUserId);
    if (!$otherUser) {
        return $this->render('error/404', [], 'error');
    }
    
    // Récupérer les messages
    $messages = $this->messageManager->getConversationMessages(
        $currentUserId, 
        $otherUserId
    );
    
    // Marquer comme lus
    $this->messageManager->markConversationAsRead($currentUserId, $otherUserId);
    
    $this->render('message/conversation', [
        'otherUser' => $otherUser,
        'messages' => $messages
    ]);
}
```

**Requête messages** :
```php
public function getConversationMessages($userId1, $userId2) {
    $sql = "SELECT m.*, 
                   s.username as sender_username,
                   s.avatar as sender_avatar
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            WHERE (m.sender_id = ? AND m.recipient_id = ?)
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.created_at ASC";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
    
    $messages = [];
    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $message = new Message();
        $message->hydrate($data);
        $messages[] = $message;
    }
    
    return $messages;
}
```

**Affichage** :
```php
<div class="conversation-container">
    <div class="conversation-header">
        <a href="<?= url('profil/' . $otherUser->getId()) ?>">
            <?php if ($otherUser->getAvatar()): ?>
                <img src="<?= url('uploads/' . h($otherUser->getAvatar())) ?>">
            <?php endif; ?>
            <h2><?= h($otherUser->getUsername()) ?></h2>
        </a>
    </div>
    
    <div class="messages-list" id="messagesList">
        <?php foreach ($messages as $message): ?>
            <div class="message <?= $message->getSenderId() == Session::get('user_id') ? 'sent' : 'received' ?>">
                <div class="message-avatar">
                    <?php if ($message->getSenderAvatar()): ?>
                        <img src="<?= url('uploads/' . h($message->getSenderAvatar())) ?>">
                    <?php endif; ?>
                </div>
                <div class="message-content">
                    <p><?= nl2br(h($message->getContent())) ?></p>
                    <span class="time"><?= formatDate($message->getCreatedAt()) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <form id="messageForm" class="message-form">
        <input type="hidden" name="recipient_id" value="<?= $otherUser->getId() ?>">
        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
        
        <textarea name="content" 
                  id="messageContent" 
                  placeholder="Votre message..."
                  required
                  maxlength="1000"></textarea>
        
        <div class="form-footer">
            <span class="char-count">
                <span id="charCount">0</span>/1000
            </span>
            <button type="submit">Envoyer</button>
        </div>
    </form>
</div>
```

---

## Envoi de message (AJAX)

### Route : `/messages/send` (POST)

**Contrôleur** :
```php
public function send() {
    requireAuth();
    
    // Accepter JSON ou POST classique
    $data = $_POST;
    if (empty($data)) {
        $data = json_decode(file_get_contents('php://input'), true);
    }
    
    // Validation CSRF
    if (!Session::validateCsrfToken($data['csrf_token'] ?? '')) {
        return $this->json(['error' => 'Token invalide'], 403);
    }
    
    $recipientId = $data['recipient_id'] ?? null;
    $content = trim($data['content'] ?? '');
    
    // Validation
    if (!$recipientId || empty($content)) {
        return $this->json(['error' => 'Données manquantes'], 400);
    }
    
    if (strlen($content) > 1000) {
        return $this->json(['error' => 'Message trop long'], 400);
    }
    
    // Vérifier que le destinataire existe
    $recipient = $this->userManager->getUserById($recipientId);
    if (!$recipient) {
        return $this->json(['error' => 'Destinataire introuvable'], 404);
    }
    
    // Envoyer le message
    $messageId = $this->messageManager->sendMessage(
        Session::get('user_id'),
        $recipientId,
        $content
    );
    
    if ($messageId) {
        return $this->json([
            'success' => true,
            'message_id' => $messageId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    return $this->json(['error' => 'Erreur d\'envoi'], 500);
}
```

**Manager** :
```php
public function sendMessage($senderId, $recipientId, $content) {
    $sql = "INSERT INTO messages (sender_id, recipient_id, content, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$senderId, $recipientId, $content]);
    
    return $this->db->lastInsertId();
}
```

**JavaScript** :
```javascript
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const content = formData.get('content');
    
    if (!content.trim()) {
        return;
    }
    
    try {
        const response = await fetch('<?= url("messages/send") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                recipient_id: formData.get('recipient_id'),
                content: content,
                csrf_token: formData.get('csrf_token')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Ajouter le message à la liste
            addMessageToList(data.content, data.created_at, true);
            
            // Réinitialiser le formulaire
            this.reset();
            document.getElementById('charCount').textContent = '0';
            
            // Scroll vers le bas
            scrollToBottom();
        } else {
            alert(data.error || 'Erreur d\'envoi');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    }
});

function addMessageToList(content, time, isSent) {
    const messagesList = document.getElementById('messagesList');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
    
    messageDiv.innerHTML = `
        <div class="message-content">
            <p>${escapeHtml(content)}</p>
            <span class="time">${formatTime(time)}</span>
        </div>
    `;
    
    messagesList.appendChild(messageDiv);
}

function scrollToBottom() {
    const messagesList = document.getElementById('messagesList');
    messagesList.scrollTop = messagesList.scrollHeight;
}

// Compteur de caractères
document.getElementById('messageContent').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});
```

---

## Nouveau message

### Route : `/messages/compose/{recipientId}`

Permet d'initier une conversation depuis :
- La page détail d'un livre
- Le profil public d'un utilisateur

**Contrôleur** :
```php
public function compose($recipientId) {
    requireAuth();
    
    $recipient = $this->userManager->getUserById($recipientId);
    if (!$recipient) {
        return $this->render('error/404', [], 'error');
    }
    
    // Vérifier si une conversation existe déjà
    $hasConversation = $this->messageManager->hasConversation(
        Session::get('user_id'),
        $recipientId
    );
    
    // Si conversation existe, rediriger
    if ($hasConversation) {
        return $this->redirect('messages/conversation/' . $recipientId);
    }
    
    // Récupérer le contexte (livre) si présent
    $bookId = $_GET['book'] ?? null;
    $book = null;
    if ($bookId) {
        $book = $this->bookManager->getBookById($bookId);
    }
    
    $this->render('message/compose', [
        'recipient' => $recipient,
        'book' => $book
    ]);
}
```

**Formulaire** :
```php
<div class="compose-container">
    <h1>Nouveau message à <?= h($recipient->getUsername()) ?></h1>
    
    <?php if ($book): ?>
        <div class="context-book">
            <p>À propos du livre :</p>
            <div class="book-preview">
                <?php if ($book->getImage()): ?>
                    <img src="<?= url('uploads/' . h($book->getImage())) ?>">
                <?php endif; ?>
                <div>
                    <h3><?= h($book->getTitle()) ?></h3>
                    <p><?= h($book->getAuthor()) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?= url('messages/send') ?>">
        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
        <input type="hidden" name="recipient_id" value="<?= $recipient->getId() ?>">
        <input type="hidden" name="redirect" value="1">
        
        <div class="form-group">
            <label for="content">Votre message</label>
            <textarea id="content" 
                      name="content" 
                      rows="8" 
                      required 
                      maxlength="1000"
                      placeholder="Bonjour, je suis intéressé(e) par votre livre..."></textarea>
            <small><span id="charCount">0</span>/1000 caractères</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Envoyer</button>
            <a href="<?= url('nos-livres') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
```

---

## Marquer comme lu

```php
public function markConversationAsRead($currentUserId, $otherUserId) {
    $sql = "UPDATE messages 
            SET is_read = 1 
            WHERE recipient_id = ? 
              AND sender_id = ? 
              AND is_read = 0";
    
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$currentUserId, $otherUserId]);
}
```

---

## Compteur de messages non lus

### Dans le header

```php
<?php
$unreadCount = 0;
if (Session::get('user_id')) {
    $messageManager = new MessageManager();
    $unreadCount = $messageManager->getUnreadCount(Session::get('user_id'));
}
?>

<a href="<?= url('messages') ?>">
    Messages
    <?php if ($unreadCount > 0): ?>
        <span class="badge"><?= $unreadCount ?></span>
    <?php endif; ?>
</a>
```

**Manager** :
```php
public function getUnreadCount($userId) {
    $stmt = $this->db->prepare(
        "SELECT COUNT(*) as count 
         FROM messages 
         WHERE recipient_id = ? AND is_read = 0"
    );
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result['count'] ?? 0;
}
```

---

## Intégration depuis les livres

### Bouton dans `/livre/{id}`

```php
<?php if (!$isOwner && Session::get('user_id')): ?>
    <?php if ($book->getIsAvailable()): ?>
        <a href="<?= url('messages/compose/' . $book->getUserId() . '?book=' . $book->getId()) ?>" 
           class="btn btn-primary">
            📧 Envoyer un message
        </a>
    <?php else: ?>
        <span class="badge unavailable">Livre non disponible</span>
    <?php endif; ?>
<?php elseif (!Session::get('user_id')): ?>
    <a href="<?= url('login') ?>" class="btn btn-secondary">
        Se connecter pour contacter
    </a>
<?php endif; ?>
```

---

## Sécurité

### ✅ Protections implémentées

- **CSRF tokens** sur tous les envois
- **Authentification requise** pour toutes les routes
- **Validation longueur** (max 1000 caractères)
- **Échappement HTML** dans l'affichage
- **Vérification destinataire** existe
- **Requêtes préparées** SQL

### Limitations

```php
// Limite de taille
if (strlen($content) > 1000) {
    return $this->json(['error' => 'Message trop long'], 400);
}

// Empêcher spam (optionnel)
$lastMessage = $this->getLastMessageTime($userId);
if (time() - $lastMessage < 2) { // 2 secondes minimum
    return $this->json(['error' => 'Veuillez patienter'], 429);
}
```

---

## Tests

### Scénarios de test

1. **Liste conversations**
   - Sans messages → Message vide
   - Avec messages → Liste affichée
   - Badge non lus affiché

2. **Envoi message**
   - Nouveau message → Conversation créée
   - Réponse → Message ajouté
   - AJAX fonctionne → Pas de rechargement

3. **Lecture**
   - Ouvrir conversation → Messages marqués lus
   - Badge disparaît

4. **Validation**
   - Message vide → Erreur
   - Message trop long → Erreur
   - Token invalide → Erreur

---

## CSS suggéré

```css
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
    max-height: 500px;
    overflow-y: auto;
}

.message {
    display: flex;
    gap: 0.5rem;
}

.message.sent {
    flex-direction: row-reverse;
}

.message.sent .message-content {
    background: #007bff;
    color: white;
    border-radius: 18px 18px 0 18px;
}

.message.received .message-content {
    background: #f1f1f1;
    color: #333;
    border-radius: 18px 18px 18px 0;
}

.message-content {
    max-width: 70%;
    padding: 0.75rem 1rem;
}

.unread-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
```

---

## Améliorations possibles

- Notifications en temps réel (WebSocket)
- Suppression de messages
- Modification de messages
- Réactions (emoji)
- Pièces jointes (images)
- Messages de groupe
- Archivage conversations
- Recherche dans messages
- Typing indicator ("en train d'écrire...")

---

## Résumé

Le système de messagerie offre :

✅ **Conversations** : Gestion complète
✅ **Temps réel** : Envoi AJAX sans rechargement
✅ **Notifications** : Compteur messages non lus
✅ **Intégration** : Depuis livres et profils
✅ **Sécurité** : CSRF, validation, échappement

**Prochaine étape** : **07-DEVELOPPEMENT.md** pour les bonnes pratiques de développement.
