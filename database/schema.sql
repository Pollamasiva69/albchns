-- Base de datos para el juego Balls vs Blocks
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS balls_vs_blocks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE balls_vs_blocks;

-- Tabla de jugadores
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de puntuaciones
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

-- Tabla de récords globales (top scores)
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

-- Vista para el ranking global
CREATE OR REPLACE VIEW global_ranking AS
SELECT
    l.id,
    p.username,
    l.best_score,
    l.best_level,
    l.total_games,
    l.last_updated,
    RANK() OVER (ORDER BY l.best_score DESC) as ranking
FROM leaderboard l
INNER JOIN players p ON l.player_id = p.id
ORDER BY l.best_score DESC
LIMIT 100;
