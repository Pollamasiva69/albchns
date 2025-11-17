<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['username']) || !isset($data['score'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
        exit;
    }

    $username = trim($data['username']);
    $score = (int)$data['score'];
    $level = isset($data['level']) ? (int)$data['level'] : 1;
    $ballsCount = isset($data['ballsCount']) ? (int)$data['ballsCount'] : 1;

    // Validaciones
    if (empty($username) || strlen($username) > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nombre de usuario inválido']);
        exit;
    }

    if ($score < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Puntuación inválida']);
        exit;
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // Buscar o crear jugador
    $stmt = $pdo->prepare('SELECT id FROM players WHERE username = ?');
    $stmt->execute([$username]);
    $player = $stmt->fetch();

    if (!$player) {
        $stmt = $pdo->prepare('INSERT INTO players (username) VALUES (?)');
        $stmt->execute([$username]);
        $playerId = $pdo->lastInsertId();
    } else {
        $playerId = $player['id'];
    }

    // Guardar puntuación
    $stmt = $pdo->prepare('INSERT INTO scores (player_id, score, level_reached, balls_count) VALUES (?, ?, ?, ?)');
    $stmt->execute([$playerId, $score, $level, $ballsCount]);

    // Actualizar o crear registro en leaderboard
    $stmt = $pdo->prepare('SELECT id, best_score, total_games FROM leaderboard WHERE player_id = ?');
    $stmt->execute([$playerId]);
    $leaderboardEntry = $stmt->fetch();

    if (!$leaderboardEntry) {
        $stmt = $pdo->prepare('INSERT INTO leaderboard (player_id, best_score, best_level, total_games) VALUES (?, ?, ?, 1)');
        $stmt->execute([$playerId, $score, $level]);
        $isNewRecord = true;
    } else {
        $isNewRecord = $score > $leaderboardEntry['best_score'];
        if ($isNewRecord) {
            $stmt = $pdo->prepare('UPDATE leaderboard SET best_score = ?, best_level = ?, total_games = total_games + 1 WHERE player_id = ?');
            $stmt->execute([$score, $level, $playerId]);
        } else {
            $stmt = $pdo->prepare('UPDATE leaderboard SET total_games = total_games + 1 WHERE player_id = ?');
            $stmt->execute([$playerId]);
        }
    }

    // Obtener ranking actual del jugador
    $stmt = $pdo->prepare('
        SELECT COUNT(*) + 1 as ranking
        FROM leaderboard
        WHERE best_score > (SELECT best_score FROM leaderboard WHERE player_id = ?)
    ');
    $stmt->execute([$playerId]);
    $rankingData = $stmt->fetch();
    $ranking = $rankingData['ranking'];

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'isNewRecord' => $isNewRecord,
        'ranking' => $ranking,
        'totalGames' => ($leaderboardEntry['total_games'] ?? 0) + 1
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar la puntuación']);
}
?>
