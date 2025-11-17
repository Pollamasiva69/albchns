<?php
// Ejemplo de configuración de base de datos
// Copia este archivo como config.php y ajusta los valores

define('DB_HOST', 'localhost');        // Host de la base de datos
define('DB_NAME', 'balls_vs_blocks');  // Nombre de la base de datos
define('DB_USER', 'root');             // Usuario de MySQL
define('DB_PASS', '');                 // Contraseña de MySQL
define('DB_CHARSET', 'utf8mb4');

// Función para crear conexión PDO
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error de conexión a la base de datos'
        ]);
        exit;
    }
}
?>
