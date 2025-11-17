@echo off
REM Script de instalación automática para Balls vs Blocks (Windows)
chcp 65001 > nul
cls

echo.
echo ╔═══════════════════════════════════════════════════╗
echo ║                                                   ║
echo ║     ⚡ BALLS VS BLOCKS - INSTALADOR ⚡          ║
echo ║          Configuración Automática                ║
echo ║                                                   ║
echo ╚═══════════════════════════════════════════════════╝
echo.

REM Verificar que estamos en el directorio correcto
if not exist "database\schema.sql" (
    echo ❌ Error: No se encuentra database\schema.sql
    echo    Asegúrate de ejecutar este script desde la raíz del proyecto
    pause
    exit /b 1
)

echo ¿Usar configuración rápida? (root/1234) [S/n]:
set /p QUICK_MODE=""
if "%QUICK_MODE%"=="" set QUICK_MODE=S

if /i "%QUICK_MODE%"=="S" (
    REM Configuración rápida
    set DB_NAME=balls_vs_blocks_game
    set DB_HOST=localhost
    set DB_USER=root
    set DB_PASS=1234
    set DB_PORT=3306

    echo.
    echo ✅ Usando configuración rápida:
    echo   DB: balls_vs_blocks_game
    echo   Usuario: root
    echo   Contraseña: ****
    echo.
) else (
    echo Por favor, proporciona los siguientes datos:
    echo.

    REM Solicitar datos de configuración
    set /p DB_NAME="Nombre de la base de datos [balls_vs_blocks_game]: "
    if "%DB_NAME%"=="" set DB_NAME=balls_vs_blocks_game

    set /p DB_HOST="Host de MySQL [localhost]: "
    if "%DB_HOST%"=="" set DB_HOST=localhost

    set /p DB_USER="Usuario de MySQL [root]: "
    if "%DB_USER%"=="" set DB_USER=root

    set /p DB_PASS="Contraseña de MySQL [1234]: "
    if "%DB_PASS%"=="" set DB_PASS=1234

    set /p DB_PORT="Puerto de MySQL [3306]: "
    if "%DB_PORT%"=="" set DB_PORT=3306
)

echo.
echo ═══════════════════════════════════════════════════
echo Configuración:
echo   Base de datos: %DB_NAME%
echo   Host: %DB_HOST%
echo   Usuario: %DB_USER%
echo   Puerto: %DB_PORT%
echo ═══════════════════════════════════════════════════
echo.

set /p CONFIRM="¿Continuar con la instalación? (S/N): "
if /i not "%CONFIRM%"=="S" (
    echo ⚠️  Instalación cancelada
    pause
    exit /b 0
)

echo.
echo ℹ️  Iniciando instalación...
echo.

REM Verificar si MySQL está instalado
where mysql >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ❌ MySQL no está instalado o no está en el PATH
    echo    Instala MySQL e intenta de nuevo
    echo.
    echo    Para agregar MySQL al PATH:
    echo    1. Busca la carpeta de instalación de MySQL (ej: C:\Program Files\MySQL\MySQL Server 8.0\bin)
    echo    2. Agrégala a las variables de entorno PATH
    pause
    exit /b 1
)

echo ✅ MySQL encontrado
echo.

REM Crear comando MySQL
if "%DB_PASS%"=="" (
    set MYSQL_CMD=mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER%
) else (
    set MYSQL_CMD=mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -p%DB_PASS%
)

REM Probar conexión
echo ℹ️  Probando conexión a MySQL...
%MYSQL_CMD% -e "SELECT 1;" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ❌ No se pudo conectar a MySQL
    echo    Verifica tus credenciales e intenta de nuevo
    pause
    exit /b 1
)

echo ✅ Conexión a MySQL exitosa
echo.

REM Crear base de datos
echo ℹ️  Creando base de datos '%DB_NAME%'...
%MYSQL_CMD% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ❌ Error al crear la base de datos
    pause
    exit /b 1
)

echo ✅ Base de datos creada
echo.

REM Importar esquema
echo ℹ️  Importando esquema de base de datos...

REM Crear archivo temporal con el schema modificado
powershell -Command "(Get-Content 'database\schema.sql') -replace 'balls_vs_blocks', '%DB_NAME%' | Set-Content 'database\schema_temp.sql'"

%MYSQL_CMD% < database\schema_temp.sql >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ❌ Error al importar el esquema
    del database\schema_temp.sql >nul 2>nul
    pause
    exit /b 1
)

del database\schema_temp.sql >nul 2>nul
echo ✅ Esquema importado correctamente
echo.

REM Crear archivo de configuración
echo ℹ️  Creando archivo de configuración...

(
echo ^<?php
echo // Configuración de la base de datos
echo // Generado automáticamente por install.bat
echo.
echo define^('DB_HOST', '%DB_HOST%'^);
echo define^('DB_NAME', '%DB_NAME%'^);
echo define^('DB_USER', '%DB_USER%'^);
echo define^('DB_PASS', '%DB_PASS%'^);
echo define^('DB_CHARSET', 'utf8mb4'^);
echo.
echo // Crear conexión PDO
echo function getDbConnection^(^) {
echo     try {
echo         $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
echo         $options = [
echo             PDO::ATTR_ERRMODE            =^> PDO::ERRMODE_EXCEPTION,
echo             PDO::ATTR_DEFAULT_FETCH_MODE =^> PDO::FETCH_ASSOC,
echo             PDO::ATTR_EMULATE_PREPARES   =^> false,
echo         ];
echo.
echo         $pdo = new PDO^($dsn, DB_USER, DB_PASS, $options^);
echo         return $pdo;
echo     } catch ^(PDOException $e^) {
echo         http_response_code^(500^);
echo         echo json_encode^([
echo             'success' =^> false,
echo             'error' =^> 'Error de conexión a la base de datos'
echo         ]^);
echo         exit;
echo     }
echo }
echo ?^>
) > database\config.php

echo ✅ Archivo config.php creado
echo.

REM Insertar datos de prueba
set /p INSERT_TEST="¿Deseas insertar datos de prueba? (S/N): "
if /i "%INSERT_TEST%"=="S" (
    echo ℹ️  Insertando datos de prueba...

    (
    echo INSERT IGNORE INTO players ^(username^) VALUES
    echo     ^('TestPlayer1'^),
    echo     ^('ProGamer'^),
    echo     ^('BallMaster'^),
    echo     ^('BlockDestroyer'^),
    echo     ^('Champion'^);
    echo.
    echo INSERT INTO scores ^(player_id, score, level_reached, balls_count^) VALUES
    echo     ^(1, 5000, 50, 25^),
    echo     ^(2, 8500, 85, 42^),
    echo     ^(3, 12000, 120, 68^),
    echo     ^(4, 6500, 65, 35^),
    echo     ^(5, 15000, 150, 99^);
    echo.
    echo INSERT INTO leaderboard ^(player_id, best_score, best_level, total_games^) VALUES
    echo     ^(1, 5000, 50, 10^),
    echo     ^(2, 8500, 85, 25^),
    echo     ^(3, 12000, 120, 50^),
    echo     ^(4, 6500, 65, 15^),
    echo     ^(5, 15000, 150, 100^);
    ) > database\test_data.sql

    %MYSQL_CMD% %DB_NAME% < database\test_data.sql >nul 2>nul
    del database\test_data.sql >nul 2>nul

    echo ✅ Datos de prueba insertados
    echo.
)

REM Resumen final
echo.
echo ╔═══════════════════════════════════════════════════╗
echo ║                                                   ║
echo ║         ¡INSTALACIÓN COMPLETADA! ✅              ║
echo ║                                                   ║
echo ╚═══════════════════════════════════════════════════╝
echo.
echo Información de la instalación:
echo   📁 Base de datos: %DB_NAME%
echo   🖥️  Host: %DB_HOST%:%DB_PORT%
echo   👤 Usuario: %DB_USER%
echo   📄 Config: database\config.php
echo.
echo Próximos pasos:
echo   1. Inicia el servidor: php -S localhost:8000
echo   2. Prueba la API: http://localhost:8000/test-api.html
echo   3. Juega: http://localhost:8000/
echo.
echo ¡Disfruta del juego! 🎮
echo.
pause
