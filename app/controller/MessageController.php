<?php

/**
 * Contrôleur de gestion de la messagerie
 * 
 * Gère les conversations entre utilisateurs :
 * - Affichage de la liste des conversations
 * - Affichage d'une conversation spécifique
 * - Envoi de messages
 */

class MessageController extends Controller
{
    private MessageManager $messageManager;
    private UserManager $userManager;

    public function __construct()
    {
        $this->messageManager = $this->loadManager('Message');
        $this->userManager = $this->loadManager('User');
    }

    /* ================================
       ACTIONS PUBLIQUES - CONSULTATION
       ================================ */

    /**
     * Affiche la liste des conversations de l'utilisateur connecté
     * Route : messagerie
     */
    public function index(): void
    {
        $this->requireAuth();

        $userId = Session::getUserId();

        $conversations = $this->messageManager->getConversations($userId);
        $unreadCount = $this->messageManager->getUnreadCount($userId);

        $this->render('messagerie/empty', [
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
            'userId' => $userId,
            'title' => 'Messagerie - Tom Troc'
        ]);
    }

    /**
     * Affiche une conversation spécifique avec un autre utilisateur
     * Route : messagerie/conversation/{id}
     */
    public function conversation(?int $otherUserId = null): void
    {
        $this->requireAuth();

        $userId = Session::getUserId();

        // Valider le destinataire
        $errors = $this->validateRecipient($userId, $otherUserId);
        
        if (!empty($errors)) {
            Session::setFlash('error', implode(', ', $errors));
            $this->redirect('messagerie');
            return;
        }

        // Récupérer l'utilisateur (on sait qu'il existe car validateRecipient() l'a vérifié)
        $otherUser = $this->userManager->findById($otherUserId);

        // Récupérer les messages de la conversation
        $messages = $this->messageManager->getConversationMessages($userId, $otherUserId);

        // Marquer la conversation comme lue
        $this->messageManager->markConversationAsRead($userId, $otherUserId);

        // Récupérer toutes les conversations pour la sidebar
        $conversations = $this->messageManager->getConversations($userId);

        $this->render('messagerie/conversation', [
            'messages' => $messages,
            'otherUser' => $otherUser,
            'userId' => $userId,
            'conversations' => $conversations,
            'csrfToken' => Session::generateCsrfToken(),
            'title' => 'Chat w/ ' . $otherUser->getUsername() . ' - Tom Troc'
        ]);
    }

    /* ================================
       ACTIONS - ENVOI DE MESSAGES
       ================================ */

    /**
     * Envoie un nouveau message dans une conversation (POST)
     * Route : messagerie/send
     */
    public function send(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('messagerie');
            return;
        }

        $this->validateCsrf('messagerie');

        $userId = Session::getUserId();
        $recipientId = (int) ($_POST['recipient_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        // Validation des données
        $errors = $this->validateMessage($userId, $recipientId, $content);

        if (!empty($errors)) {
            Session::setFlash('error', implode(', ', $errors));
            
            if ($recipientId) {
                $this->redirect('messagerie/conversation/' . $recipientId);
            } else {
                $this->redirect('messagerie');
            }
            return;
        }

        // Envoi du message
        $messageId = $this->messageManager->sendMessage($userId, $recipientId, $content);

        if (!$messageId) {
            Session::setFlash('error', 'Erreur lors de l\'envoi du message.');
        }

        $this->redirect('messagerie/conversation/' . $recipientId);
    }

    /* ================================
       MÉTHODES INTERNES - VALIDATION
       ================================ */

    /**
     * Valide un destinataire de message
     * 
     * @param int $userId ID de l'utilisateur qui envoie
     * @param int|null $recipientId ID du destinataire
     * @return array Tableau d'erreurs (vide si tout est valide)
     */

    private function validateRecipient(int $userId, ?int $recipientId): array
    {
        $errors = [];

        // Vérifier qu'on n'essaie pas de s'envoyer un message à soi-même
        if (!$recipientId || $recipientId === $userId) {
            $errors[] = 'Destinataire invalide.';
            return $errors;
        }

        // Vérifier que le destinataire existe
        $recipient = $this->userManager->findById($recipientId);
        if (!$recipient) {
            $errors[] = 'Destinataire introuvable.';
        }

        return $errors;
    }

    /**
     * Valide les données d'un message
     * 
     * @param int $userId ID de l'utilisateur qui envoie
     * @param int $recipientId ID du destinataire
     * @param string $content Contenu du message
     * @return array Tableau d'erreurs (vide si tout est valide)
     */

    private function validateMessage(int $userId, int $recipientId, string $content): array
    {
        $errors = [];

        // Validation du contenu
        if (empty($content)) {
            $errors[] = 'Le message ne peut pas être vide.';
        } elseif (strlen($content) > 1000) {
            $errors[] = 'Le message ne peut pas dépasser 1000 caractères.';
        }

        // Validation du destinataire
        $recipientErrors = $this->validateRecipient($userId, $recipientId);
        $errors = array_merge($errors, $recipientErrors);

        return $errors;
    }
}
