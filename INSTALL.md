# 📦 Guía de Instalación Rápida

## ⚡ Instalación en 5 Minutos

### Paso 1: Descargar el Proyecto
```bash
git clone <url-del-repositorio>
cd albchns
```

### Paso 2: Configurar Base de Datos

**Opción A: Desde línea de comandos**
```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE balls_vs_blocks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar esquema
mysql -u root -p balls_vs_blocks < database/schema.sql
```

**Opción B: Desde phpMyAdmin**
1. Accede a phpMyAdmin
2. Crea una nueva base de datos llamada `balls_vs_blocks`
3. Selecciona la base de datos
4. Ve a la pestaña "Importar"
5. Selecciona el archivo `database/schema.sql`
6. Haz clic en "Continuar"

### Paso 3: Configurar Conexión a DB

```bash
# Copia el archivo de ejemplo
cp database/config.example.php database/config.php

# Edita con tus credenciales
nano database/config.php  # o usa tu editor preferido
```

Edita estos valores:
```php
define('DB_HOST', 'localhost');        // Host de MySQL
define('DB_NAME', 'balls_vs_blocks');  // Nombre de la DB
define('DB_USER', 'tu_usuario');       // Tu usuario MySQL
define('DB_PASS', 'tu_contraseña');    // Tu contraseña MySQL
```

### Paso 4: Iniciar Servidor

**Para desarrollo local:**
```bash
# Opción 1: PHP built-in server
php -S localhost:8000

# Opción 2: Python
python -m http.server 8000

# Opción 3: Node.js http-server
npx http-server -p 8000
```

**Para XAMPP/WAMP/MAMP:**
1. Copia la carpeta del proyecto a `htdocs/` o `www/`
2. Inicia Apache y MySQL desde el panel de control
3. Accede a `http://localhost/albchns/`

### Paso 5: Verificar Instalación

1. Abre tu navegador
2. Ve a `http://localhost:8000/test-api.html`
3. Haz clic en todos los botones de prueba
4. Verifica que todas las pruebas pasen ✅

Si todas las pruebas pasan, ¡estás listo! Ve a `http://localhost:8000/` para jugar.

## 🔧 Solución de Problemas Comunes

### Error: "Access denied for user"
- Verifica las credenciales en `database/config.php`
- Asegúrate de que el usuario MySQL tiene permisos
```sql
GRANT ALL PRIVILEGES ON balls_vs_blocks.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Table doesn't exist"
- Importa el esquema de nuevo:
```bash
mysql -u root -p balls_vs_blocks < database/schema.sql
```

### Error: "Cannot connect to API"
- Verifica que el servidor PHP esté corriendo
- Revisa la consola del navegador (F12) para ver errores
- Asegúrate de que `API_BASE_URL` en `game.js` sea correcto

### La página se ve bien pero no guarda puntuaciones
- Abre `test-api.html` y prueba el botón "Guardar Puntuación"
- Revisa los errores en la consola del navegador
- Verifica permisos de los archivos PHP:
```bash
chmod 644 api/*.php
chmod 644 database/*.php
```

## 🚀 Despliegue en Producción

### Requisitos del Servidor
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Módulos PHP: pdo, pdo_mysql
- Apache con mod_rewrite (o Nginx)

### Checklist de Seguridad
- [ ] Cambiar credenciales de base de datos
- [ ] Usar HTTPS (SSL/TLS)
- [ ] Configurar CORS apropiadamente
- [ ] Limitar rate de requests a la API
- [ ] Habilitar gzip/deflate
- [ ] Configurar headers de seguridad
- [ ] Hacer backup regular de la DB
- [ ] Actualizar PHP y MySQL regularmente

### Nginx Configuration Example
```nginx
server {
    listen 80;
    server_name tudominio.com;
    root /path/to/albchns;
    index index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~* \.(sql|md)$ {
        deny all;
    }
}
```

## 📞 Soporte

Si tienes problemas:
1. Revisa el archivo `README.md` para más detalles
2. Usa `test-api.html` para diagnosticar problemas
3. Revisa la consola del navegador (F12) para errores JavaScript
4. Revisa los logs de PHP/Apache/Nginx

## ✅ Lista de Verificación Post-Instalación

- [ ] Base de datos creada e importada
- [ ] Archivo `database/config.php` configurado
- [ ] Servidor web funcionando
- [ ] `test-api.html` - Todas las pruebas pasan
- [ ] Puedes ver la pantalla de inicio del juego
- [ ] Puedes jugar una partida
- [ ] Las puntuaciones se guardan correctamente
- [ ] El leaderboard muestra puntuaciones
- [ ] El juego funciona en móvil

¡Felicidades! Tu juego Balls vs Blocks está instalado y listo para jugar. 🎮🎉
