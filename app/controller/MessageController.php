<?php

class MessageController extends Controller {
    
    private $messageManager;
    private $userManager;

    /**
     * Charge le MessageManager si pas encore fait
     */
    private function getMessageManager() {
        if (!$this->messageManager) {
            $this->messageManager = $this->loadManager('Message');
        }
        return $this->messageManager;
    }

    /**
     * Charge le UserManager si pas encore fait
     */
    private function getUserManager() {
        if (!$this->userManager) {
            $this->userManager = $this->loadManager('User');
        }
        return $this->userManager;
    }

    /**
     * Affiche la liste des conversations de l'utilisateur connecté
     */
    public function index() {
        // Vérifier que l'utilisateur est connecté
        if (!Session::isLoggedIn()) {
            $this->error('Vous devez être connecté pour accéder à vos messages', '/login');
        }

        // Récupérer l'utilisateur actuel
        $userId = Session::getUserId();
        $currentUser = $this->getUserManager()->findById($userId);
        
        $conversations = $this->getMessageManager()->getConversations($userId);
        $unreadCount = $this->getMessageManager()->getUnreadCount($userId);

        $this->render('message/index', [
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
            'currentUser' => $currentUser,
            'pageTitle' => 'Mes messages'
        ]);
    }

    /**
     * Affiche une conversation spécifique
     */
    public function conversation($otherUserId = null) {
        // Vérifier que l'utilisateur est connecté
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté pour accéder aux conversations');
            $this->redirect('/login');
        }

        if (!$otherUserId) {
            Session::setFlash('error', 'Conversation introuvable');
            $this->redirect('/messages');
        }

        // Récupérer l'utilisateur actuel
        $userId = Session::getUserId();
        $currentUser = $this->getUserManager()->findById($userId);

        // Vérifier que l'autre utilisateur existe
        $otherUser = $this->getUserManager()->findById($otherUserId);
        if (!$otherUser) {
            Session::setFlash('error', 'Utilisateur introuvable');
            $this->redirect('/messages');
        }

        // Récupérer les messages de la conversation
        $messages = $this->getMessageManager()->getConversationMessages($userId, $otherUserId);
        
        // Marquer la conversation comme lue
        $this->getMessageManager()->markConversationAsRead($userId, $otherUserId);

        // Récupérer toutes les conversations pour la sidebar
        $conversations = $this->getMessageManager()->getConversations($userId);

        $this->render('message/conversation', [
            'messages' => $messages,
            'otherUser' => $otherUser,
            'currentUser' => $currentUser,
            'conversations' => $conversations,
            'csrfToken' => Session::generateCsrfToken(),
            'pageTitle' => 'Conversation avec ' . $otherUser->getUsername()
        ]);
    }

    /**
     * Envoie un nouveau message
     */
    public function send() {
        // Vérifier que l'utilisateur est connecté
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté');
            $this->redirect('login');
            return;
        }

        // Vérifier que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('messages');
            return;
        }

        // Vérifier le token CSRF
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token de sécurité invalide');
            $this->redirect('messages');
            return;
        }

        $userId = Session::getUserId();
        $recipientId = (int) ($_POST['recipient_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        // Validation
        $errors = [];
        
        if (empty($content)) {
            $errors[] = 'Le message ne peut pas être vide';
        }
        
        if (strlen($content) > 1000) {
            $errors[] = 'Le message ne peut pas dépasser 1000 caractères';
        }

        if (!$recipientId || $recipientId === $userId) {
            $errors[] = 'Destinataire invalide';
        }

        // Vérifier que le destinataire existe
        $recipient = $this->getUserManager()->findById($recipientId);
        if (!$recipient) {
            $errors[] = 'Destinataire introuvable';
        }

        if (empty($errors)) {
            $messageId = $this->getMessageManager()->sendMessage(
                $userId,
                $recipientId,
                $content
            );

            if ($messageId) {
                $this->redirect('messages/conversation/' . $recipientId);
                return;
            } else {
                $errors[] = 'Erreur lors de l\'envoi du message';
            }
        }

        // En cas d'erreur, rediriger vers la conversation avec le message d'erreur
        Session::setFlash('error', implode(', ', $errors));
        if ($recipientId) {
            $this->redirect('messages/conversation/' . $recipientId);
        } else {
            $this->redirect('messages');
        }
    }

    /**
     * Démarre une nouvelle conversation (depuis une page de livre par exemple)
     */
    public function compose($recipientId = null) {
        // Vérifier que l'utilisateur est connecté
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vous devez être connecté pour envoyer un message');
            $this->redirect('login');
            return;
        }

        if (!$recipientId) {
            Session::setFlash('error', 'Destinataire non spécifié');
            $this->redirect('messages');
            return;
        }

        $userId = Session::getUserId();

        // Vérifier que le destinataire existe
        $recipient = $this->getUserManager()->findById($recipientId);
        if (!$recipient) {
            Session::setFlash('error', 'Destinataire introuvable');
            $this->redirect('messages');
            return;
        }

        // Vérifier qu'on n'essaie pas de s'envoyer un message à soi-même
        if ($recipientId == $userId) {
            Session::setFlash('error', 'Vous ne pouvez pas vous envoyer un message à vous-même');
            $this->redirect('messages');
            return;
        }

        // Rediriger directement vers la conversation (même si elle est vide)
        $this->redirect('messages/conversation/' . $recipientId);
    }
}