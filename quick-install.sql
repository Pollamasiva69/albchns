-- Quick Install SQL para Balls vs Blocks
-- Ejecuta este archivo directamente en MySQL para una instalación rápida
-- Uso: mysql -u root -p < quick-install.sql

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS balls_vs_blocks_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE balls_vs_blocks_game;

-- Crear tabla de jugadores
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de puntuaciones
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    score INT NOT NULL,
    level_reached INT NOT NULL DEFAULT 1,
    balls_count INT NOT NULL DEFAULT 1,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    INDEX idx_score (score DESC),
    INDEX idx_player_score (player_id, score DESC),
    INDEX idx_played_at (played_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de leaderboard
CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    best_score INT NOT NULL,
    best_level INT NOT NULL DEFAULT 1,
    total_games INT NOT NULL DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    UNIQUE KEY unique_player (player_id),
    INDEX idx_best_score (best_score DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de prueba
INSERT IGNORE INTO players (username) VALUES
    ('TestPlayer1'),
    ('ProGamer'),
    ('BallMaster'),
    ('BlockDestroyer'),
    ('Champion');

INSERT INTO scores (player_id, score, level_reached, balls_count) VALUES
    (1, 5000, 50, 25),
    (2, 8500, 85, 42),
    (3, 12000, 120, 68),
    (4, 6500, 65, 35),
    (5, 15000, 150, 99);

INSERT INTO leaderboard (player_id, best_score, best_level, total_games) VALUES
    (1, 5000, 50, 10),
    (2, 8500, 85, 25),
    (3, 12000, 120, 50),
    (4, 6500, 65, 15),
    (5, 15000, 150, 100)
ON DUPLICATE KEY UPDATE
    best_score = VALUES(best_score),
    best_level = VALUES(best_level),
    total_games = VALUES(total_games);

-- Mostrar tablas creadas
SHOW TABLES;

-- Mostrar datos de prueba
SELECT 'Leaderboard instalado exitosamente!' as mensaje;
SELECT
    p.username,
    l.best_score,
    l.best_level,
    l.total_games
FROM leaderboard l
INNER JOIN players p ON l.player_id = p.id
ORDER BY l.best_score DESC;
