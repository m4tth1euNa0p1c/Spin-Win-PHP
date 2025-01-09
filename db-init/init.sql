-- Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS casino_db;
USE casino_db;

-- Créer la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    coins INT NOT NULL DEFAULT 60, -- Ajout de la colonne coins
    is_active TINYINT(1) DEFAULT 1,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exemple d'ajout d'un utilisateur admin (mot de passe hashé)
-- Remplace '<hashed_password>' par un mot de passe hashé réel
-- INSERT INTO users (username, email, password, is_active, role, coins) 
-- VALUES ('admin', 'admin@example.com', '$2y$10$...', 1, 'admin', 100);

-- Créer l'utilisateur 'casino_user'@'localhost' avec le plugin d'authentification recommandé
CREATE USER IF NOT EXISTS 'casino_user'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'super_secure_user_password';

-- Accorder tous les privilèges sur la base de données 'casino_db' à 'casino_user'@'localhost'
GRANT ALL PRIVILEGES ON casino_db.* TO 'casino_user'@'localhost';

-- Accorder tous les privilèges sur la base de données 'casino_db' à 'casino_user'@'%'
GRANT ALL PRIVILEGES ON casino_db.* TO 'casino_user'@'%';

-- Appliquer les changements de privilèges
FLUSH PRIVILEGES;
