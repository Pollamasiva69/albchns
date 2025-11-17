# ⚡ Instalación Rápida en 30 Segundos

## 🚀 Método 1: Script Automático (Recomendado)

```bash
./install.sh
# Presiona Enter para usar root/1234
# ¡Listo en 10 segundos!
```

## ⚡ Método 2: SQL Directo (Más Rápido)

Si ya tienes MySQL configurado con root/1234:

```bash
# 1. Instalar base de datos
mysql -u root -p1234 < quick-install.sql

# 2. Iniciar servidor
php -S 0.0.0.0:8000

# 3. Jugar
# Ve a http://localhost:8000/
```

## 🌐 Método 3: Instalador Web

Si usas XAMPP/WAMP/MAMP:

1. Abre `http://localhost/albchns/install.php`
2. Los campos ya tienen root/1234 precargados
3. Click en "INSTALAR"
4. ¡Listo!

## 📝 Configuración Actual

El juego viene preconfigurado con:
- **Usuario:** root
- **Contraseña:** 1234
- **Base de datos:** balls_vs_blocks_game
- **Host:** localhost
- **Puerto:** 3306

Si tus credenciales son diferentes, edita `database/config.php`

## ✅ Verificar Instalación

```bash
# Probar API
curl http://localhost:8000/api/leaderboard.php

# O abrir en navegador
http://localhost:8000/test-api.html
```

## 🎮 Jugar Ahora

```bash
php -S 0.0.0.0:8000
```

Abre: `http://localhost:8000/`

---

**¿Problemas?** Lee [INSTALL.md](INSTALL.md) para instrucciones detalladas.
