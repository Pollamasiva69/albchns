# 🎮 Balls vs Blocks - Juego HTML5

Un emocionante juego HTML5 estilo "Balls Versus Blocks" completamente optimizado para dispositivos móviles con sistema de puntuación global y base de datos.

> **⚡ ¿Quieres empezar YA?** Lee [QUICKSTART.md](QUICKSTART.md) para instalación en 30 segundos.

## 🌟 Características

- ✅ **Totalmente optimizado para móviles** - Controles táctiles intuitivos
- ✅ **Efectos visuales impresionantes** - Partículas, animaciones y gradientes
- ✅ **Sistema de física realista** - Rebotes y colisiones precisas
- ✅ **Power-ups de bolas adicionales** - Aumenta tu arsenal
- ✅ **Sistema de puntuación global** - Compite con jugadores de todo el mundo
- ✅ **Tabla de clasificación** - Top 20 mejores puntuaciones
- ✅ **Efectos de sonido** - Feedback auditivo mediante Web Audio API
- ✅ **Diseño responsive** - Se adapta a cualquier tamaño de pantalla
- ✅ **Base de datos MySQL** - Almacenamiento persistente de puntuaciones

## 📁 Estructura del Proyecto

```
.
├── index.html             # Página principal del juego
├── style.css              # Estilos y animaciones
├── game.js                # Lógica del juego
├── install.sh             # Instalador automático (Linux/Mac)
├── install.bat            # Instalador automático (Windows)
├── install.php            # Instalador web desde navegador
├── test-api.html          # Herramienta de prueba de API
├── api/
│   ├── save-score.php     # API para guardar puntuaciones
│   └── leaderboard.php    # API para obtener clasificación
└── database/
    ├── schema.sql         # Esquema de base de datos
    ├── config.php         # Configuración de conexión (generado)
    └── config.example.php # Ejemplo de configuración
```

## 🚀 Instalación

### ⚡ Instalación Automática (RECOMENDADO)

La forma más fácil de instalar es usando los scripts automáticos:

**Linux/Mac:**
```bash
chmod +x install.sh
./install.sh
```

**Windows:**
```batch
install.bat
```

**Desde el Navegador (XAMPP/WAMP/MAMP):**
1. Copia el proyecto a `htdocs/` o `www/`
2. Inicia Apache y MySQL
3. Ve a `http://localhost/albchns/install.php`
4. Sigue el asistente de instalación

El instalador automático:
- ✅ Crea la base de datos con nombre personalizado
- ✅ Importa el esquema completo
- ✅ Genera `database/config.php` automáticamente
- ✅ Verifica la instalación
- ✅ Inserta datos de prueba (opcional)

### 📝 Instalación Manual

Si prefieres instalar manualmente, consulta [INSTALL.md](INSTALL.md) para instrucciones detalladas.

### Iniciar el Servidor

**Opción A: PHP built-in server**
```bash
php -S localhost:8000
```

**Opción B: XAMPP/WAMP/MAMP**
- Ya está listo si instalaste con `install.php`
- Accede a `http://localhost/albchns/`

### Verificar Instalación

Abre `http://localhost:8000/test-api.html` y prueba todas las funciones

## 🎯 Cómo Jugar

1. **Inicio**: Ingresa tu nombre y presiona "JUGAR"
2. **Apuntar**: Toca y arrastra desde la parte inferior de la pantalla hacia arriba
3. **Disparar**: Suelta para lanzar las bolas en la dirección apuntada
4. **Objetivo**: Destruye todos los bloques antes de que lleguen al fondo
5. **Power-ups**: Recoge las bolas azules (+) para aumentar tu cantidad de bolas
6. **Puntuación**: Gana puntos por cada bloque destruido

## 🎨 Características Técnicas

### Frontend
- **Canvas HTML5** - Renderizado de gráficos 2D
- **Vanilla JavaScript** - Sin dependencias externas
- **CSS3 Animations** - Transiciones suaves
- **Touch Events** - Soporte completo para móviles
- **LocalStorage** - Guardado local de mejor puntuación

### Backend
- **PHP 7.4+** - API RESTful
- **MySQL 5.7+** - Base de datos relacional
- **PDO** - Consultas preparadas y seguras
- **JSON** - Formato de intercambio de datos

### Optimizaciones Móviles
- Viewport configurado para móviles
- Prevención de zoom y scroll
- Touch events optimizados
- Canvas responsive
- Rendimiento 60 FPS

## 🎮 Controles

### Móvil/Tablet
- **Toca y arrastra**: Apuntar
- **Suelta**: Disparar

### Escritorio
- **Click y arrastra**: Apuntar
- **Suelta**: Disparar

## 📊 Base de Datos

### Tablas

**players**
- `id`: ID único del jugador
- `username`: Nombre del jugador (único)
- `created_at`: Fecha de registro

**scores**
- `id`: ID único de la puntuación
- `player_id`: Referencia al jugador
- `score`: Puntuación obtenida
- `level_reached`: Nivel alcanzado
- `balls_count`: Cantidad de bolas al terminar
- `played_at`: Fecha y hora de la partida

**leaderboard**
- `id`: ID único
- `player_id`: Referencia al jugador
- `best_score`: Mejor puntuación
- `best_level`: Nivel máximo alcanzado
- `total_games`: Total de partidas jugadas
- `last_updated`: Última actualización

## 🔧 Configuración Avanzada

### Modificar Dificultad

En `game.js`, puedes ajustar:

```javascript
const CONFIG = {
    BALL_SPEED: 8,              // Velocidad de las bolas (mayor = más rápido)
    BLOCKS_PER_ROW: 5,          // Bloques por fila
    MAX_BALLS: 99,              // Máximo de bolas permitidas
    PARTICLE_COUNT: 15,         // Partículas por explosión
    // ...
};
```

### Personalizar Colores

Edita `style.css` para cambiar el esquema de colores:

```css
/* Color principal */
.btn-primary {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
}

/* Fondo */
body {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}
```

## 🌐 Despliegue en Producción

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Soporte HTTPS (recomendado)

### Pasos
1. Sube todos los archivos al servidor
2. Importa el esquema de base de datos
3. Configura las credenciales en `database/config.php`
4. Ajusta `API_BASE_URL` en `game.js`
5. Configura permisos adecuados para archivos PHP

### Seguridad
- Usa HTTPS en producción
- Cambia las credenciales de base de datos
- Implementa rate limiting en la API
- Valida todas las entradas de usuario
- Mantén PHP y MySQL actualizados

## 📱 Compatibilidad

### Navegadores Soportados
- ✅ Chrome/Edge (móvil y escritorio)
- ✅ Safari (iOS y macOS)
- ✅ Firefox (móvil y escritorio)
- ✅ Opera
- ✅ Samsung Internet

### Sistemas Operativos
- ✅ Android 5.0+
- ✅ iOS 10+
- ✅ Windows 7+
- ✅ macOS 10.10+
- ✅ Linux (todas las distribuciones modernas)

## 🐛 Solución de Problemas

### La base de datos no se conecta
- Verifica las credenciales en `database/config.php`
- Asegúrate de que MySQL esté ejecutándose
- Comprueba que la base de datos existe

### Las puntuaciones no se guardan
- Revisa la consola del navegador para errores
- Verifica que la API esté accesible
- Comprueba los permisos de los archivos PHP

### El juego no carga en móvil
- Asegúrate de usar HTTPS (algunos features requieren conexión segura)
- Verifica la compatibilidad del navegador
- Limpia la caché del navegador

### Problemas de rendimiento
- Reduce `PARTICLE_COUNT` en la configuración
- Disminuye el número de bloques por fila
- Verifica que no haya otros procesos consumiendo recursos

## 🤝 Contribuciones

¡Las contribuciones son bienvenidas! Si quieres mejorar el juego:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👨‍💻 Autor

Creado con ❤️ para proporcionar una experiencia de juego divertida y competitiva.

## 🎯 Roadmap Futuro

- [ ] Modo multijugador en tiempo real
- [ ] Más tipos de power-ups (escudo, ralentización, etc.)
- [ ] Temas visuales personalizables
- [ ] Sistema de logros y medallas
- [ ] Compartir puntuaciones en redes sociales
- [ ] Modo torneo semanal
- [ ] Música de fondo
- [ ] Efectos de sonido mejorados

---

**¡Disfruta del juego y compite por el primer lugar en la tabla de clasificación! 🏆**
