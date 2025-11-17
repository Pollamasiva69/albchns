<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Rutas de archivos JSON
$leaderboardFile = __DIR__ . '/../data/leaderboard.json';

// Crear carpeta y archivo si no existen
if (!is_dir(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0777, true);
}

if (!file_exists($leaderboardFile)) {
    file_put_contents($leaderboardFile, '[]');
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

    // Leer leaderboard actual
    $leaderboard = json_decode(file_get_contents($leaderboardFile), true) ?: [];

    // Buscar jugador existente
    $playerIndex = -1;
    foreach ($leaderboard as $index => $player) {
        if ($player['username'] === $username) {
            $playerIndex = $index;
            break;
        }
    }

    $isNewRecord = false;

    if ($playerIndex === -1) {
        // Nuevo jugador
        $leaderboard[] = [
            'username' => $username,
            'best_score' => $score,
            'best_level' => $level,
            'total_games' => 1,
            'last_updated' => date('Y-m-d H:i:s')
        ];
        $isNewRecord = true;
    } else {
        // Jugador existente
        $isNewRecord = $score > $leaderboard[$playerIndex]['best_score'];

        if ($isNewRecord) {
            $leaderboard[$playerIndex]['best_score'] = $score;
            $leaderboard[$playerIndex]['best_level'] = $level;
        }

        $leaderboard[$playerIndex]['total_games']++;
        $leaderboard[$playerIndex]['last_updated'] = date('Y-m-d H:i:s');
    }

    // Ordenar por mejor puntuación
    usort($leaderboard, function($a, $b) {
        return $b['best_score'] - $a['best_score'];
    });

    // Calcular ranking
    $ranking = 1;
    $totalGames = 1;
    foreach ($leaderboard as $index => $player) {
        if ($player['username'] === $username) {
            $ranking = $index + 1;
            $totalGames = $player['total_games'];
            break;
        }
    }

    // Guardar leaderboard
    file_put_contents($leaderboardFile, json_encode($leaderboard, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'isNewRecord' => $isNewRecord,
        'ranking' => $ranking,
        'totalGames' => $totalGames
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar la puntuación: ' . $e->getMessage()]);
}
?>
