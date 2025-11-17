#!/bin/bash

# Script de instalación automática para Balls vs Blocks
# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════╗"
echo "║                                                   ║"
echo "║     ⚡ BALLS VS BLOCKS - INSTALADOR ⚡          ║"
echo "║          Configuración Automática                ║"
echo "║                                                   ║"
echo "╚═══════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""

# Función para mostrar mensajes
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "database/schema.sql" ]; then
    print_error "Error: No se encuentra database/schema.sql"
    print_info "Asegúrate de ejecutar este script desde la raíz del proyecto"
    exit 1
fi

# Solicitar datos de configuración
echo -e "${YELLOW}Por favor, proporciona los siguientes datos:${NC}"
echo ""

# Nombre de la base de datos
read -p "Nombre de la base de datos [balls_vs_blocks_game]: " DB_NAME
DB_NAME=${DB_NAME:-balls_vs_blocks_game}

# Host
read -p "Host de MySQL [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

# Usuario
read -p "Usuario de MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

# Contraseña
read -sp "Contraseña de MySQL: " DB_PASS
echo ""

# Puerto
read -p "Puerto de MySQL [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Configuración:${NC}"
echo -e "  Base de datos: ${GREEN}$DB_NAME${NC}"
echo -e "  Host: ${GREEN}$DB_HOST${NC}"
echo -e "  Usuario: ${GREEN}$DB_USER${NC}"
echo -e "  Puerto: ${GREEN}$DB_PORT${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

read -p "¿Continuar con la instalación? (s/n): " CONFIRM
if [[ ! $CONFIRM =~ ^[Ss]$ ]]; then
    print_warning "Instalación cancelada"
    exit 0
fi

echo ""
print_info "Iniciando instalación..."
echo ""

# Verificar si MySQL está instalado
if ! command -v mysql &> /dev/null; then
    print_error "MySQL no está instalado o no está en el PATH"
    print_info "Instala MySQL e intenta de nuevo"
    exit 1
fi

print_success "MySQL encontrado"

# Verificar conexión a MySQL
echo ""
print_info "Probando conexión a MySQL..."

if [ -z "$DB_PASS" ]; then
    MYSQL_CMD="mysql -h $DB_HOST -P $DB_PORT -u $DB_USER"
else
    MYSQL_CMD="mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS"
fi

# Test de conexión
if ! $MYSQL_CMD -e "SELECT 1;" &> /dev/null; then
    print_error "No se pudo conectar a MySQL"
    print_info "Verifica tus credenciales e intenta de nuevo"
    exit 1
fi

print_success "Conexión a MySQL exitosa"

# Crear base de datos
echo ""
print_info "Creando base de datos '$DB_NAME'..."

if $MYSQL_CMD -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
    print_success "Base de datos creada"
else
    print_error "Error al crear la base de datos"
    exit 1
fi

# Importar esquema
echo ""
print_info "Importando esquema de base de datos..."

# Modificar el schema.sql temporalmente para usar el nombre correcto de DB
TEMP_SCHEMA=$(mktemp)
sed "s/balls_vs_blocks/$DB_NAME/g" database/schema.sql > "$TEMP_SCHEMA"

if $MYSQL_CMD < "$TEMP_SCHEMA" 2>/dev/null; then
    print_success "Esquema importado correctamente"
    rm "$TEMP_SCHEMA"
else
    print_error "Error al importar el esquema"
    rm "$TEMP_SCHEMA"
    exit 1
fi

# Verificar tablas creadas
echo ""
print_info "Verificando tablas..."

TABLES=$($MYSQL_CMD -D "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | grep -v "Tables_in" | wc -l)

if [ "$TABLES" -ge 3 ]; then
    print_success "Tablas creadas correctamente ($TABLES tablas)"
else
    print_warning "Se esperaban 3 tablas, pero se encontraron $TABLES"
fi

# Crear archivo de configuración
echo ""
print_info "Creando archivo de configuración..."

cat > database/config.php <<EOF
<?php
// Configuración de la base de datos
// Generado automáticamente por install.sh

define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_CHARSET', 'utf8mb4');

// Crear conexión PDO
function getDbConnection() {
    try {
        \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        \$options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        return \$pdo;
    } catch (PDOException \$e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error de conexión a la base de datos'
        ]);
        exit;
    }
}
?>
EOF

print_success "Archivo config.php creado"

# Probar conexión desde PHP
echo ""
print_info "Probando conexión desde PHP..."

PHP_TEST=$(php -r "
require 'database/config.php';
try {
    \$pdo = getDbConnection();
    echo 'OK';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
")

if [[ $PHP_TEST == "OK" ]]; then
    print_success "Conexión PHP funcional"
else
    print_error "Error en conexión PHP: $PHP_TEST"
fi

# Insertar datos de prueba (opcional)
echo ""
read -p "¿Deseas insertar datos de prueba? (s/n): " INSERT_TEST
if [[ $INSERT_TEST =~ ^[Ss]$ ]]; then
    print_info "Insertando datos de prueba..."

    $MYSQL_CMD -D "$DB_NAME" <<EOF
INSERT INTO players (username) VALUES
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
    (5, 15000, 150, 100);
EOF

    print_success "Datos de prueba insertados"
fi

# Resumen final
echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                   ║${NC}"
echo -e "${GREEN}║         ¡INSTALACIÓN COMPLETADA! ✅              ║${NC}"
echo -e "${GREEN}║                                                   ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}Información de la instalación:${NC}"
echo -e "  📁 Base de datos: ${GREEN}$DB_NAME${NC}"
echo -e "  🖥️  Host: ${GREEN}$DB_HOST:$DB_PORT${NC}"
echo -e "  👤 Usuario: ${GREEN}$DB_USER${NC}"
echo -e "  📄 Config: ${GREEN}database/config.php${NC}"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo -e "  1. Inicia el servidor: ${BLUE}php -S localhost:8000${NC}"
echo -e "  2. Prueba la API: ${BLUE}http://localhost:8000/test-api.html${NC}"
echo -e "  3. Juega: ${BLUE}http://localhost:8000/${NC}"
echo ""
echo -e "${GREEN}¡Disfruta del juego! 🎮${NC}"
echo ""
