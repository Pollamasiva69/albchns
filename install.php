<?php
/**
 * Instalador Web para Balls vs Blocks
 * Accede a este archivo desde tu navegador para configurar la base de datos automáticamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Variables de estado
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'install') {
        $dbHost = trim($_POST['db_host']);
        $dbName = trim($_POST['db_name']);
        $dbUser = trim($_POST['db_user']);
        $dbPass = $_POST['db_pass'];
        $dbPort = isset($_POST['db_port']) ? intval($_POST['db_port']) : 3306;
        $insertTestData = isset($_POST['insert_test_data']);

        try {
            // Conectar a MySQL (sin especificar base de datos)
            $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Crear base de datos
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Seleccionar base de datos
            $pdo->exec("USE `$dbName`");

            // Leer y ejecutar schema.sql
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');

            // Reemplazar el nombre de la base de datos en el schema
            $schema = str_replace('balls_vs_blocks', $dbName, $schema);

            // Separar por declaraciones
            $statements = array_filter(
                array_map('trim', explode(';', $schema)),
                function($stmt) {
                    return !empty($stmt) &&
                           !preg_match('/^(--|\/\*)/', $stmt) &&
                           !preg_match('/^CREATE DATABASE/', $stmt) &&
                           !preg_match('/^USE /', $stmt);
                }
            );

            // Ejecutar cada declaración
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignorar errores de "CREATE OR REPLACE VIEW" si ya existe
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            throw $e;
                        }
                    }
                }
            }

            // Insertar datos de prueba si se solicitó
            if ($insertTestData) {
                $testData = "
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
                ";

                foreach (explode(';', $testData) as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                    }
                }
            }

            // Crear archivo de configuración
            $configContent = "<?php\n";
            $configContent .= "// Configuración de la base de datos\n";
            $configContent .= "// Generado automáticamente por install.php\n\n";
            $configContent .= "define('DB_HOST', " . var_export($dbHost, true) . ");\n";
            $configContent .= "define('DB_NAME', " . var_export($dbName, true) . ");\n";
            $configContent .= "define('DB_USER', " . var_export($dbUser, true) . ");\n";
            $configContent .= "define('DB_PASS', " . var_export($dbPass, true) . ");\n";
            $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
            $configContent .= "// Crear conexión PDO\n";
            $configContent .= "function getDbConnection() {\n";
            $configContent .= "    try {\n";
            $configContent .= "        \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n";
            $configContent .= "        \$options = [\n";
            $configContent .= "            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n";
            $configContent .= "            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
            $configContent .= "            PDO::ATTR_EMULATE_PREPARES   => false,\n";
            $configContent .= "        ];\n\n";
            $configContent .= "        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);\n";
            $configContent .= "        return \$pdo;\n";
            $configContent .= "    } catch (PDOException \$e) {\n";
            $configContent .= "        http_response_code(500);\n";
            $configContent .= "        echo json_encode([\n";
            $configContent .= "            'success' => false,\n";
            $configContent .= "            'error' => 'Error de conexión a la base de datos'\n";
            $configContent .= "        ]);\n";
            $configContent .= "        exit;\n";
            $configContent .= "    }\n";
            $configContent .= "}\n";
            $configContent .= "?>\n";

            file_put_contents(__DIR__ . '/database/config.php', $configContent);

            // Verificar que todo funciona
            require __DIR__ . '/database/config.php';
            $testPdo = getDbConnection();

            // Contar tablas
            $tables = $testPdo->query("SHOW TABLES")->fetchAll();
            $tableCount = count($tables);

            $step = 2;
            $success = "¡Instalación completada exitosamente! Se crearon $tableCount tablas.";

        } catch (PDOException $e) {
            $error = "Error de base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Balls vs Blocks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(255, 107, 107, 0.3);
        }

        h1 {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
            text-shadow: 0 0 20px rgba(255, 107, 107, 0.5);
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #ff6b6b;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 107, 107, 0.5);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #ff6b6b;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(255, 107, 107, 0.3);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }

        .help-text {
            font-size: 0.85em;
            color: #aaa;
            margin-top: 5px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 25px rgba(255, 107, 107, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.2);
            border: 2px solid #ff6b6b;
            color: #ffaaaa;
        }

        .alert-success {
            background: rgba(78, 205, 196, 0.2);
            border: 2px solid #4ecdc4;
            color: #4ecdc4;
        }

        .success-icon {
            font-size: 4em;
            text-align: center;
            margin: 20px 0;
        }

        .info-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #aaa;
        }

        .info-value {
            color: #ff6b6b;
            font-weight: bold;
        }

        .links {
            margin-top: 30px;
            text-align: center;
        }

        .links a {
            display: inline-block;
            margin: 5px 10px;
            color: #4ecdc4;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .links a:hover {
            color: #ff6b6b;
            transform: translateY(-2px);
        }

        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }

        .step.active {
            background: #ff6b6b;
            box-shadow: 0 0 20px rgba(255, 107, 107, 0.5);
        }

        .step.completed {
            background: #4ecdc4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚡ BALLS VS BLOCKS ⚡</h1>
        <p class="subtitle">Instalador Automático de Base de Datos</p>

        <div class="steps">
            <div class="step <?php echo $step == 1 ? 'active' : ($step > 1 ? 'completed' : ''); ?>">1</div>
            <div class="step <?php echo $step == 2 ? 'active' : ''; ?>">2</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="POST" action="install.php">
                <input type="hidden" name="action" value="install">

                <div class="form-group">
                    <label>Nombre de la Base de Datos *</label>
                    <input type="text" name="db_name" value="balls_vs_blocks_game" required>
                    <div class="help-text">Nombre único para tu base de datos del juego</div>
                </div>

                <div class="form-group">
                    <label>Host de MySQL *</label>
                    <input type="text" name="db_host" value="localhost" required>
                    <div class="help-text">Normalmente "localhost" o "127.0.0.1"</div>
                </div>

                <div class="form-group">
                    <label>Puerto de MySQL</label>
                    <input type="number" name="db_port" value="3306" min="1" max="65535">
                    <div class="help-text">Puerto por defecto: 3306</div>
                </div>

                <div class="form-group">
                    <label>Usuario de MySQL *</label>
                    <input type="text" name="db_user" value="root" required>
                    <div class="help-text">Usuario con permisos para crear bases de datos</div>
                </div>

                <div class="form-group">
                    <label>Contraseña de MySQL</label>
                    <input type="password" name="db_pass" value="1234">
                    <div class="help-text">Por defecto: 1234 (déjalo vacío si no tienes contraseña)</div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="insert_test_data" id="test_data" checked>
                        <label for="test_data" style="margin: 0;">Insertar datos de prueba</label>
                    </div>
                    <div class="help-text" style="margin-left: 30px;">
                        Crea 5 jugadores de ejemplo con puntuaciones para probar
                    </div>
                </div>

                <button type="submit" class="btn">🚀 INSTALAR BASE DE DATOS</button>
            </form>

        <?php else: ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>

            <div class="success-icon">🎉</div>

            <div class="info-box">
                <h3 style="color: #4ecdc4; margin-bottom: 15px;">✅ Instalación Completada</h3>
                <div class="info-item">
                    <span class="info-label">Archivo de configuración:</span>
                    <span class="info-value">database/config.php</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">✅ Listo para usar</span>
                </div>
            </div>

            <div class="links">
                <h3 style="color: #ff6b6b; margin-bottom: 15px;">Próximos Pasos:</h3>
                <a href="test-api.html">🧪 Probar API</a>
                <a href="index.html">🎮 Jugar Ahora</a>
                <a href="test-api.html">📊 Ver Leaderboard</a>
            </div>

            <button onclick="location.href='install.php'" class="btn btn-secondary" style="margin-top: 30px;">
                🔄 Reinstalar
            </button>
        <?php endif; ?>
    </div>
</body>
</html>
