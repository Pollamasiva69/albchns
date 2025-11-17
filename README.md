# 🎮 Balls vs Blocks - Juego HTML5

Un emocionante juego HTML5 estilo "Balls Versus Blocks" completamente optimizado para dispositivos móviles con sistema de puntuación global.

> **⚡ Sin MySQL, sin instalación, sin complicaciones.**

## 🌟 Características

- ✅ **Optimizado para móviles** - Controles táctiles intuitivos
- ✅ **Efectos visuales** - Partículas, animaciones y gradientes
- ✅ **Sistema de física realista** - Rebotes y colisiones precisas
- ✅ **Power-ups** - Aumenta tu arsenal de bolas
- ✅ **Ranking global** - Compite con otros jugadores
- ✅ **Almacenamiento JSON** - Sin MySQL, solo archivos
- ✅ **Instalación cero** - Funciona directamente

## 🚀 Cómo Usar

### Si tienes Apache/Nginx (Lo más común)

```bash
# 1. Copiar archivos a tu servidor web
cp -r albchns /var/www/html/

# 2. Dar permisos a la carpeta de datos
chmod 777 /var/www/html/albchns/data

# 3. Abrir en navegador
# http://localhost/albchns/
# o
# http://tu-ip/albchns/
```

**¡Listo!** Funciona en el puerto 80 normal. No necesitas comandos raros.

### Si NO tienes servidor web (Desarrollo rápido)

```bash
# Solo para desarrollo temporal
cd albchns
php -S 0.0.0.0:8000

# Abre: http://localhost:8000/
```

**Nota:** Este método usa puerto 8000 porque:
- El puerto 80 requiere permisos root
- Es solo para desarrollo, no para producción

### XAMPP/WAMP/MAMP

1. Copia carpeta a `htdocs/`
2. Inicia Apache desde el panel
3. Abre `http://localhost/albchns/`

**Puerto 80 normal**, sin comandos.

## 📁 Estructura

```
albchns/
├── index.html              # Juego
├── style.css               # Estilos
├── game.js                 # Lógica
├── api/
│   ├── save-score.php      # Guardar puntuaciones
│   └── leaderboard.php     # Ver ranking
└── data/
    └── leaderboard.json    # Datos (JSON simple)
```

## 🎯 Cómo Jugar

1. Ingresa tu nombre
2. Arrastra para apuntar
3. Suelta para disparar
4. Destruye bloques
5. Recoge power-ups (+)
6. ¡Compite por el #1!

## 📊 Sistema de Datos

Todo en un archivo JSON simple:

**`data/leaderboard.json`**
```json
[{
    "username": "Player1",
    "best_score": 15000,
    "best_level": 150,
    "total_games": 100
}]
```

**Ventajas:**
- ✅ Sin MySQL
- ✅ Fácil de respaldar: `cp data/leaderboard.json backup/`
- ✅ Portátil: copia y pega
- ✅ Editable con cualquier editor

## 🔧 Configuración

### Dificultad (game.js)
```javascript
const CONFIG = {
    BALL_SPEED: 8,        // Velocidad de bolas
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
- ✅ Todos los sistemas operativos

## 🐛 Solución de Problemas

### Las puntuaciones no se guardan
```bash
chmod 777 data/
```

### "Permission denied" en puerto 80
Usa Apache/Nginx en lugar de `php -S`, o usa otro puerto:
```bash
php -S 0.0.0.0:8000  # Puerto 8000 (sin permisos root)
```

## 🎮 Controles

- **Móvil**: Toca y arrastra
- **PC**: Click y arrastra

## 📄 Licencia

MIT License - Código abierto

---

**¡A jugar! 🎮**
