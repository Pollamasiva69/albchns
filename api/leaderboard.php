<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;
    $username = isset($_GET['username']) ? trim($_GET['username']) : null;

    // Leer leaderboard
    $leaderboard = json_decode(file_get_contents($leaderboardFile), true) ?: [];

    // Ordenar por mejor puntuación
    usort($leaderboard, function($a, $b) {
        return $b['best_score'] - $a['best_score'];
    });

    // Agregar ranking a cada jugador
    foreach ($leaderboard as $index => &$player) {
        $player['ranking'] = $index + 1;
    }
    unset($player); // Romper referencia

    // Limitar resultados
    $topScores = array_slice($leaderboard, 0, $limit);

    $response = [
        'success' => true,
        'leaderboard' => $topScores,
        'total' => count($topScores)
    ];

    // Si se proporciona un username, incluir su información
    if ($username) {
        foreach ($leaderboard as $player) {
            if ($player['username'] === $username) {
                $response['playerData'] = $player;
                break;
            }
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al obtener el leaderboard: ' . $e->getMessage()]);
}
?>
