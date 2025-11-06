-- ============================================
-- Base de données TomTroc
-- Système d'échange de livres entre particuliers
-- ============================================
-- 
-- INSTRUCTIONS D'INSTALLATION :
-- 1. Importer ce fichier dans phpMyAdmin ou via ligne de commande :
--    mysql -u root -p < database.sql
-- 2. La base de données "tomtroc" sera créée automatiquement
-- 3. Les tables et données de test seront insérées
--
-- COMPTES DE TEST (mot de passe : "password") :
-- - alice@example.com
-- - bob@example.com
-- - charlie@example.com
-- ============================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tomtroc;

-- ============================================
-- Table users - Utilisateurs de la plateforme
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================
-- Table books - Livres des utilisateurs
-- ============================================
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_title (title),
    INDEX idx_is_available (is_available),
    CONSTRAINT fk_books_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Table messages - Messagerie entre utilisateurs
-- ============================================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_conversation (sender_id, recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_messages_sender 
        FOREIGN KEY (sender_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_messages_recipient 
        FOREIGN KEY (recipient_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Données de test (optionnel pour le développement)
-- ============================================

-- Utilisateurs de test (mot de passe : "password123" hashé)
INSERT INTO users (username, email, password) VALUES
('alice', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('bob', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('charlie', 'charlie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Livres de test
INSERT INTO books (user_id, title, author, description, is_available) VALUES
(1, 'Le Seigneur des Anneaux', 'J.R.R. Tolkien', 'L\'épopée fantasy culte qui a redéfini le genre. Trilogie complète en excellent état.', 1),
(1, '1984', 'George Orwell', 'Un roman dystopique incontournable sur le totalitarisme et la surveillance.', 1),
(2, 'Harry Potter à l\'école des sorciers', 'J.K. Rowling', 'Le premier tome de la saga magique qui a enchanté des millions de lecteurs.', 1),
(2, 'Le Petit Prince', 'Antoine de Saint-Exupéry', 'Un conte philosophique et poétique pour petits et grands.', 0),
(3, 'Dune', 'Frank Herbert', 'Chef-d\'œuvre de science-fiction sur fond de politique et d\'écologie.', 1),
(3, 'Les Misérables', 'Victor Hugo', 'Monument de la littérature française. Édition annotée.', 1);

-- Messages de test
INSERT INTO messages (sender_id, recipient_id, content, is_read) VALUES
(1, 2, 'Bonjour ! Je suis intéressé par votre exemplaire de Harry Potter. Est-il toujours disponible ?', 1),
(2, 1, 'Oui, il est toujours disponible ! Seriez-vous intéressé par un échange ?', 1),
(1, 2, 'Absolument ! Je pourrais vous proposer Le Seigneur des Anneaux en échange. Qu\'en pensez-vous ?', 0),
(3, 1, 'Bonjour Alice, j\'ai vu que vous aviez 1984. C\'est un livre que je recherche depuis longtemps !', 0);

-- ============================================
-- Affichage de confirmation
-- ============================================
SELECT 'Base de données créée avec succès !' AS status;
SELECT COUNT(*) AS nb_users FROM users;
SELECT COUNT(*) AS nb_books FROM books;
SELECT COUNT(*) AS nb_messages FROM messages;
