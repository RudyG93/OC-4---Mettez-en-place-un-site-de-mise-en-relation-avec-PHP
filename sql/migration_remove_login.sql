-- ============================================
-- Migration : Suppression de la colonne login
-- ============================================

USE tomtroc;

-- Vérifier si la colonne login existe avant de la supprimer
SET @sql = CONCAT(
    'ALTER TABLE users DROP COLUMN IF EXISTS login'
);

-- Exécuter la suppression si la colonne existe
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'tomtroc' 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'login'
);

-- Supprimer la colonne si elle existe
DROP PROCEDURE IF EXISTS remove_login_column;

DELIMITER //
CREATE PROCEDURE remove_login_column()
BEGIN
    DECLARE column_count INT;
    
    SELECT COUNT(*) INTO column_count
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'tomtroc' 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'login';
    
    IF column_count > 0 THEN
        ALTER TABLE users DROP COLUMN login;
        SELECT 'Colonne login supprimée avec succès' AS status;
    ELSE
        SELECT 'Colonne login n\'existe pas' AS status;
    END IF;
END //
DELIMITER ;

CALL remove_login_column();
DROP PROCEDURE remove_login_column;

-- Vérifier la structure finale
DESCRIBE users;