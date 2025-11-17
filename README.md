# 🎮 Balls vs Blocks - Juego HTML5

Un emocionante juego HTML5 estilo "Balls Versus Blocks" completamente optimizado para dispositivos móviles con sistema de puntuación global.

> **⚡ ¡Sin instalación! Solo necesitas PHP.**

## 🌟 Características

- ✅ **Totalmente optimizado para móviles** - Controles táctiles intuitivos
- ✅ **Efectos visuales impresionantes** - Partículas, animaciones y gradientes
- ✅ **Sistema de física realista** - Rebotes y colisiones precisas
- ✅ **Power-ups de bolas adicionales** - Aumenta tu arsenal
- ✅ **Sistema de puntuación global** - Compite con jugadores de todo el mundo
- ✅ **Tabla de clasificación** - Top 20 mejores puntuaciones
- ✅ **Efectos de sonido** - Feedback auditivo mediante Web Audio API
- ✅ **Diseño responsive** - Se adapta a cualquier tamaño de pantalla
- ✅ **Almacenamiento JSON** - Sin necesidad de MySQL
- ✅ **Instalación cero** - Funciona directamente sin configuración

## 🚀 Inicio Rápido (30 segundos)

```bash
# 1. Clonar repositorio
git clone <tu-repo>
cd albchns

# 2. Iniciar servidor PHP
php -S 0.0.0.0:8000

# 3. Abrir navegador
# Ve a http://localhost:8000/
```

**¡Eso es todo!** No necesitas MySQL, bases de datos, ni configuración. 🎉

## 📁 Estructura

```
.
├── index.html              # Juego principal
├── style.css               # Estilos
├── game.js                 # Lógica del juego
├── api/                    # APIs PHP
│   ├── save-score.php      # Guardar puntuaciones
│   └── leaderboard.php     # Obtener clasificación
└── data/                   # Datos JSON
    └── leaderboard.json    # Puntuaciones
```

## 🎯 Cómo Jugar

1. Ingresa tu nombre
2. Toca y arrastra para apuntar
3. Suelta para disparar
4. Destruye bloques antes de que lleguen al fondo
5. Recoge power-ups (+) para más bolas
6. ¡Compite por el primer lugar!

## 🌐 Despliegue

### Apache/Nginx
```bash
cp -r albchns /var/www/html/
chmod 777 /var/www/html/albchns/data
```

### XAMPP/WAMP/MAMP
1. Copia a `htdocs/`
2. Inicia Apache
3. Ve a `http://localhost/albchns/`

## 📊 Datos

Todo se guarda en `data/leaderboard.json`:

```json
[{
    "username": "Player1",
    "best_score": 15000,
    "best_level": 150,
    "total_games": 100
}]
```

**Sin MySQL, sin complicaciones.**

## 🔧 Configuración

### Dificultad (game.js)
```javascript
const CONFIG = {
    BALL_SPEED: 8,        // Velocidad
    BLOCKS_PER_ROW: 5,    // Bloques por fila
    MAX_BALLS: 99         // Máximo de bolas
};
```

### Colores (style.css)
```css
.btn-primary {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
}
```

## 📱 Compatibilidad

- ✅ Chrome/Edge/Firefox/Safari
- ✅ iOS 10+ / Android 5.0+
- ✅ Windows/macOS/Linux

## 🐛 Solución de Problemas

**Las puntuaciones no se guardan:**
```bash
chmod 777 data/
```

**El juego no carga:**
```bash
php -v  # Verifica que PHP esté instalado
php -S localhost:8000  # Inicia el servidor
```

## 🎮 Controles

- **Móvil**: Toca y arrastra
- **PC**: Click y arrastra

## 📄 Licencia

MIT License - Código abierto

---

**¡A jugar! 🎮** Abre un issue si tienes problemas.
