<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;
    $username = isset($_GET['username']) ? trim($_GET['username']) : null;

    $pdo = getDbConnection();

    // Obtener top scores
    $stmt = $pdo->prepare('
        SELECT
            p.username,
            l.best_score,
            l.best_level,
            l.total_games,
            l.last_updated,
            (SELECT COUNT(*) + 1 FROM leaderboard l2 WHERE l2.best_score > l.best_score) as ranking
        FROM leaderboard l
        INNER JOIN players p ON l.player_id = p.id
        ORDER BY l.best_score DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    $topScores = $stmt->fetchAll();

    $response = [
        'success' => true,
        'leaderboard' => $topScores,
        'total' => count($topScores)
    ];

    // Si se proporciona un username, incluir su información
    if ($username) {
        $stmt = $pdo->prepare('
            SELECT
                p.username,
                l.best_score,
                l.best_level,
                l.total_games,
                l.last_updated,
                (SELECT COUNT(*) + 1 FROM leaderboard l2 WHERE l2.best_score > l.best_score) as ranking
            FROM leaderboard l
            INNER JOIN players p ON l.player_id = p.id
            WHERE p.username = ?
        ');
        $stmt->execute([$username]);
        $playerData = $stmt->fetch();

        if ($playerData) {
            $response['playerData'] = $playerData;
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al obtener el leaderboard']);
}
?>
