// Configuración del juego
const CONFIG = {
    API_BASE_URL: './api/', // Cambia esto según tu servidor
    BALL_RADIUS: 8,
    BALL_SPEED: 8,
    BLOCK_HEIGHT: 50,
    BLOCK_WIDTH: 70,
    BLOCK_MARGIN: 5,
    BLOCKS_PER_ROW: 5,
    BLOCK_ROWS_VISIBLE: 7,
    POWERUP_BALL_RADIUS: 10,
    MAX_BALLS: 99,
    PARTICLE_COUNT: 15,
    FPS: 60
};

// Estado del juego
const gameState = {
    currentScreen: 'start',
    username: localStorage.getItem('username') || '',
    score: 0,
    level: 1,
    ballsCount: 1,
    bestScore: parseInt(localStorage.getItem('bestScore')) || 0,
    bestLevel: parseInt(localStorage.getItem('bestLevel')) || 0,
    isPlaying: false,
    isPaused: false,
    balls: [],
    blocks: [],
    powerups: [],
    particles: [],
    ballsToShoot: 0,
    shootingInterval: null,
    allBallsReturned: true,
    shootAngle: 0,
    shootPower: 0,
    startX: 0,
    returnY: 0,
    canvas: null,
    ctx: null
};

// Clases
class Ball {
    constructor(x, y, vx, vy) {
        this.x = x;
        this.y = y;
        this.vx = vx;
        this.vy = vy;
        this.radius = CONFIG.BALL_RADIUS;
        this.isActive = true;
        this.trail = [];
    }

    update() {
        this.x += this.vx;
        this.y += this.vy;

        // Agregar rastro
        this.trail.push({ x: this.x, y: this.y });
        if (this.trail.length > 5) this.trail.shift();

        // Rebote en paredes laterales
        if (this.x - this.radius <= 0 || this.x + this.radius >= gameState.canvas.width) {
            this.vx *= -1;
            this.x = this.x < gameState.canvas.width / 2 ? this.radius : gameState.canvas.width - this.radius;
            playSound('bounce');
        }

        // Rebote en techo
        if (this.y - this.radius <= 0) {
            this.vy *= -1;
            this.y = this.radius;
            playSound('bounce');
        }

        // Retorno al inicio
        if (this.y > gameState.returnY) {
            this.isActive = false;
            this.x = gameState.startX;
            this.vx = 0;
            this.vy = 0;
        }
    }

    draw(ctx) {
        // Dibujar rastro
        ctx.globalAlpha = 0.3;
        this.trail.forEach((point, index) => {
            const alpha = (index + 1) / this.trail.length;
            ctx.globalAlpha = alpha * 0.3;
            ctx.fillStyle = '#ff6b6b';
            ctx.beginPath();
            ctx.arc(point.x, point.y, this.radius * 0.7, 0, Math.PI * 2);
            ctx.fill();
        });
        ctx.globalAlpha = 1;

        // Dibujar bola con efecto de brillo
        const gradient = ctx.createRadialGradient(
            this.x - this.radius * 0.3,
            this.y - this.radius * 0.3,
            0,
            this.x,
            this.y,
            this.radius
        );
        gradient.addColorStop(0, '#ffaaaa');
        gradient.addColorStop(0.5, '#ff6b6b');
        gradient.addColorStop(1, '#ee5a6f');

        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fill();

        // Borde
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.stroke();
    }
}

class Block {
    constructor(x, y, hits, type = 'normal') {
        this.x = x;
        this.y = y;
        this.width = CONFIG.BLOCK_WIDTH;
        this.height = CONFIG.BLOCK_HEIGHT;
        this.hits = hits;
        this.maxHits = hits;
        this.type = type;
        this.opacity = 1;
        this.scale = 1;
        this.shakeX = 0;
        this.shakeY = 0;
    }

    hit() {
        this.hits--;
        this.scale = 1.2;
        setTimeout(() => this.scale = 1, 100);

        // Shake effect
        this.shakeX = (Math.random() - 0.5) * 5;
        this.shakeY = (Math.random() - 0.5) * 5;
        setTimeout(() => {
            this.shakeX = 0;
            this.shakeY = 0;
        }, 100);

        if (this.hits <= 0) {
            gameState.score += this.maxHits;
            updateScore();
            createParticles(this.x + this.width / 2, this.y + this.height / 2, this.getColor());
            playSound('block');
            return true;
        }
        playSound('hit');
        return false;
    }

    getColor() {
        const hue = (this.hits / this.maxHits) * 120;
        return `hsl(${hue}, 70%, 60%)`;
    }

    draw(ctx) {
        ctx.save();
        ctx.globalAlpha = this.opacity;

        const x = this.x + this.shakeX;
        const y = this.y + this.shakeY;
        const centerX = x + this.width / 2;
        const centerY = y + this.height / 2;

        ctx.translate(centerX, centerY);
        ctx.scale(this.scale, this.scale);
        ctx.translate(-centerX, -centerY);

        // Fondo del bloque con gradiente
        const gradient = ctx.createLinearGradient(x, y, x, y + this.height);
        const color = this.getColor();
        gradient.addColorStop(0, color);
        gradient.addColorStop(1, this.darkenColor(color));

        ctx.fillStyle = gradient;
        ctx.fillRect(x, y, this.width, this.height);

        // Borde
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, this.width, this.height);

        // Número de golpes
        ctx.fillStyle = '#fff';
        ctx.font = 'bold 18px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(this.hits, centerX, centerY);

        ctx.restore();
    }

    darkenColor(hslColor) {
        const match = hslColor.match(/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/);
        if (match) {
            return `hsl(${match[1]}, ${match[2]}%, ${parseInt(match[3]) - 20}%)`;
        }
        return hslColor;
    }

    checkCollision(ball) {
        const closestX = Math.max(this.x, Math.min(ball.x, this.x + this.width));
        const closestY = Math.max(this.y, Math.min(ball.y, this.y + this.height));

        const distanceX = ball.x - closestX;
        const distanceY = ball.y - closestY;

        const distanceSquared = (distanceX * distanceX) + (distanceY * distanceY);

        if (distanceSquared < (ball.radius * ball.radius)) {
            // Determinar dirección del rebote
            const ballCenterX = ball.x;
            const ballCenterY = ball.y;
            const blockCenterX = this.x + this.width / 2;
            const blockCenterY = this.y + this.height / 2;

            const dx = ballCenterX - blockCenterX;
            const dy = ballCenterY - blockCenterY;

            if (Math.abs(dx / this.width) > Math.abs(dy / this.height)) {
                ball.vx *= -1;
            } else {
                ball.vy *= -1;
            }

            return true;
        }

        return false;
    }
}

class Powerup {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.radius = CONFIG.POWERUP_BALL_RADIUS;
        this.vy = 2;
        this.collected = false;
        this.rotation = 0;
    }

    update() {
        this.y += this.vy;
        this.rotation += 0.1;
    }

    draw(ctx) {
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate(this.rotation);

        const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, this.radius);
        gradient.addColorStop(0, '#4ecdc4');
        gradient.addColorStop(1, '#44a8a0');

        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(0, 0, this.radius, 0, Math.PI * 2);
        ctx.fill();

        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Símbolo +
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(-this.radius * 0.5, 0);
        ctx.lineTo(this.radius * 0.5, 0);
        ctx.moveTo(0, -this.radius * 0.5);
        ctx.lineTo(0, this.radius * 0.5);
        ctx.stroke();

        ctx.restore();
    }

    checkCollision(ball) {
        const dx = this.x - ball.x;
        const dy = this.y - ball.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        return distance < this.radius + ball.radius;
    }
}

class Particle {
    constructor(x, y, color) {
        this.x = x;
        this.y = y;
        this.vx = (Math.random() - 0.5) * 6;
        this.vy = (Math.random() - 0.5) * 6;
        this.life = 1;
        this.decay = 0.02;
        this.radius = Math.random() * 4 + 2;
        this.color = color;
    }

    update() {
        this.x += this.vx;
        this.y += this.vy;
        this.vy += 0.2; // Gravedad
        this.life -= this.decay;
    }

    draw(ctx) {
        ctx.globalAlpha = this.life;
        ctx.fillStyle = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fill();
        ctx.globalAlpha = 1;
    }
}

// Funciones de utilidad
function createParticles(x, y, color) {
    for (let i = 0; i < CONFIG.PARTICLE_COUNT; i++) {
        gameState.particles.push(new Particle(x, y, color));
    }
}

function playSound(type) {
    // Sonidos simples usando Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    gainNode.gain.value = 0.1;

    switch (type) {
        case 'hit':
            oscillator.frequency.value = 200;
            oscillator.type = 'square';
            break;
        case 'block':
            oscillator.frequency.value = 400;
            oscillator.type = 'sine';
            break;
        case 'bounce':
            oscillator.frequency.value = 150;
            oscillator.type = 'triangle';
            break;
        case 'powerup':
            oscillator.frequency.value = 600;
            oscillator.type = 'sine';
            break;
    }

    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.1);
}

// Funciones de pantalla
function showScreen(screenId) {
    document.querySelectorAll('.screen').forEach(screen => {
        screen.classList.remove('active');
    });
    document.getElementById(screenId).classList.add('active');
    gameState.currentScreen = screenId;
}

function updateScore() {
    document.getElementById('scoreValue').textContent = gameState.score;
    document.getElementById('levelValue').textContent = gameState.level;
    document.getElementById('ballsValue').textContent = gameState.ballsCount;
}

// Inicialización del juego
function initGame() {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    // Configurar canvas para móvil
    function resizeCanvas() {
        const container = document.getElementById('gameScreen');
        const rect = container.getBoundingClientRect();

        // Mantener aspecto 9:16 aproximadamente
        const maxWidth = Math.min(rect.width, 500);
        const maxHeight = rect.height - 60; // Espacio para UI

        canvas.width = maxWidth;
        canvas.height = maxHeight;

        gameState.returnY = canvas.height - 60;
        gameState.startX = canvas.width / 2;
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    gameState.canvas = canvas;
    gameState.ctx = ctx;

    // Cargar mejor puntuación
    document.getElementById('bestScoreDisplay').textContent = gameState.bestScore;
    document.getElementById('bestLevelDisplay').textContent = gameState.bestLevel;

    if (gameState.username) {
        document.getElementById('usernameInput').value = gameState.username;
    }
}

function startGame() {
    const username = document.getElementById('usernameInput').value.trim();

    if (!username) {
        alert('Por favor, ingresa tu nombre');
        return;
    }

    gameState.username = username;
    localStorage.setItem('username', username);

    // Resetear estado
    gameState.score = 0;
    gameState.level = 1;
    gameState.ballsCount = 1;
    gameState.balls = [];
    gameState.blocks = [];
    gameState.powerups = [];
    gameState.particles = [];
    gameState.allBallsReturned = true;
    gameState.isPlaying = true;

    updateScore();
    generateBlocks();
    showScreen('gameScreen');
    gameLoop();
}

function generateBlocks() {
    // Mover bloques hacia abajo
    gameState.blocks.forEach(block => {
        block.y += CONFIG.BLOCK_HEIGHT + CONFIG.BLOCK_MARGIN;
    });

    // Verificar si algún bloque llegó al fondo
    const bottomBlock = gameState.blocks.find(block =>
        block.y + block.height >= gameState.returnY
    );

    if (bottomBlock) {
        gameOver();
        return;
    }

    // Generar nueva fila
    const blocksInRow = CONFIG.BLOCKS_PER_ROW;
    const totalWidth = blocksInRow * CONFIG.BLOCK_WIDTH + (blocksInRow - 1) * CONFIG.BLOCK_MARGIN;
    const startX = (gameState.canvas.width - totalWidth) / 2;

    for (let i = 0; i < blocksInRow; i++) {
        // No crear bloques siempre, dejar espacios
        if (Math.random() > 0.7) continue;

        const x = startX + i * (CONFIG.BLOCK_WIDTH + CONFIG.BLOCK_MARGIN);
        const y = 80;
        const hits = Math.floor(Math.random() * gameState.level * 5) + gameState.level;

        gameState.blocks.push(new Block(x, y, hits));

        // Chance de powerup
        if (Math.random() > 0.85 && gameState.ballsCount < CONFIG.MAX_BALLS) {
            gameState.powerups.push(new Powerup(x + CONFIG.BLOCK_WIDTH / 2, y));
        }
    }

    gameState.level++;
    updateScore();
}

// Control de disparo
function setupControls() {
    const canvas = gameState.canvas;
    let isDragging = false;
    let startY = 0;

    function handleStart(e) {
        if (!gameState.allBallsReturned || !gameState.isPlaying) return;

        const touch = e.touches ? e.touches[0] : e;
        const rect = canvas.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;

        if (y > gameState.returnY - 50) {
            isDragging = true;
            startY = y;
        }
    }

    function handleMove(e) {
        if (!isDragging || !gameState.isPlaying) return;

        e.preventDefault();
        const touch = e.touches ? e.touches[0] : e;
        const rect = canvas.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;

        // Calcular ángulo
        const dx = x - gameState.startX;
        const dy = y - gameState.returnY;
        const angle = Math.atan2(dy, dx);

        // Solo permitir disparos hacia arriba
        if (y < gameState.returnY) {
            gameState.shootAngle = angle;

            // Mostrar línea de puntería
            const aimLine = document.getElementById('aimLine');
            const distance = Math.min(Math.sqrt(dx * dx + dy * dy), 200);
            aimLine.style.height = distance + 'px';
            aimLine.style.left = gameState.startX + 'px';
            aimLine.style.top = gameState.returnY + 'px';
            aimLine.style.transform = `rotate(${angle}rad)`;
            aimLine.style.display = 'block';
        }
    }

    function handleEnd(e) {
        if (!isDragging || !gameState.isPlaying) return;

        isDragging = false;
        document.getElementById('aimLine').style.display = 'none';

        shootBalls();
    }

    canvas.addEventListener('touchstart', handleStart);
    canvas.addEventListener('touchmove', handleMove);
    canvas.addEventListener('touchend', handleEnd);
    canvas.addEventListener('mousedown', handleStart);
    canvas.addEventListener('mousemove', handleMove);
    canvas.addEventListener('mouseup', handleEnd);
}

function shootBalls() {
    if (!gameState.allBallsReturned) return;

    gameState.allBallsReturned = false;
    gameState.ballsToShoot = gameState.ballsCount;

    const angle = gameState.shootAngle;
    const speed = CONFIG.BALL_SPEED;
    const vx = Math.cos(angle) * speed;
    const vy = Math.sin(angle) * speed;

    let shotCount = 0;
    const shootInterval = setInterval(() => {
        if (shotCount >= gameState.ballsToShoot) {
            clearInterval(shootInterval);
            return;
        }

        gameState.balls.push(new Ball(gameState.startX, gameState.returnY, vx, vy));
        shotCount++;
        playSound('bounce');
    }, 100);
}

// Game loop
function gameLoop() {
    if (!gameState.isPlaying) return;

    const ctx = gameState.ctx;
    const canvas = gameState.canvas;

    // Limpiar canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Dibujar fondo con efecto
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0, '#0f0f1e');
    gradient.addColorStop(1, '#1a1a2e');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Actualizar y dibujar bolas
    gameState.balls.forEach((ball, index) => {
        if (ball.isActive) {
            ball.update();

            // Colisiones con bloques
            gameState.blocks.forEach((block, blockIndex) => {
                if (block.checkCollision(ball)) {
                    if (block.hit()) {
                        gameState.blocks.splice(blockIndex, 1);
                    }
                }
            });

            ball.draw(ctx);
        } else {
            gameState.balls.splice(index, 1);
        }
    });

    // Verificar si todas las bolas regresaron
    if (gameState.balls.length === 0 && gameState.ballsToShoot > 0 && !gameState.allBallsReturned) {
        gameState.allBallsReturned = true;
        gameState.ballsToShoot = 0;
        generateBlocks();
    }

    // Actualizar y dibujar powerups
    gameState.powerups.forEach((powerup, index) => {
        powerup.update();

        // Verificar colisión con bolas
        gameState.balls.forEach(ball => {
            if (!powerup.collected && powerup.checkCollision(ball)) {
                powerup.collected = true;
                gameState.ballsCount = Math.min(gameState.ballsCount + 1, CONFIG.MAX_BALLS);
                updateScore();
                createParticles(powerup.x, powerup.y, '#4ecdc4');
                playSound('powerup');
            }
        });

        if (powerup.y > canvas.height || powerup.collected) {
            gameState.powerups.splice(index, 1);
        } else {
            powerup.draw(ctx);
        }
    });

    // Actualizar y dibujar partículas
    gameState.particles.forEach((particle, index) => {
        particle.update();
        if (particle.life <= 0) {
            gameState.particles.splice(index, 1);
        } else {
            particle.draw(ctx);
        }
    });

    // Dibujar bloques
    gameState.blocks.forEach(block => {
        block.draw(ctx);
    });

    // Dibujar línea de retorno
    ctx.strokeStyle = 'rgba(255, 107, 107, 0.3)';
    ctx.lineWidth = 2;
    ctx.setLineDash([10, 5]);
    ctx.beginPath();
    ctx.moveTo(0, gameState.returnY);
    ctx.lineTo(canvas.width, gameState.returnY);
    ctx.stroke();
    ctx.setLineDash([]);

    // Dibujar posición inicial
    if (gameState.allBallsReturned) {
        ctx.fillStyle = 'rgba(255, 107, 107, 0.5)';
        ctx.beginPath();
        ctx.arc(gameState.startX, gameState.returnY, CONFIG.BALL_RADIUS, 0, Math.PI * 2);
        ctx.fill();
    }

    requestAnimationFrame(gameLoop);
}

function gameOver() {
    gameState.isPlaying = false;

    // Actualizar estadísticas finales
    document.getElementById('finalScore').textContent = gameState.score;
    document.getElementById('finalLevel').textContent = gameState.level;
    document.getElementById('finalBalls').textContent = gameState.ballsCount;

    // Verificar si es nuevo récord
    const isNewRecord = gameState.score > gameState.bestScore;
    if (isNewRecord) {
        gameState.bestScore = gameState.score;
        gameState.bestLevel = gameState.level;
        localStorage.setItem('bestScore', gameState.bestScore);
        localStorage.setItem('bestLevel', gameState.bestLevel);

        const recordMsg = document.getElementById('recordMessage');
        recordMsg.textContent = '🎉 ¡NUEVO RÉCORD PERSONAL! 🎉';
        recordMsg.classList.add('show');
    } else {
        document.getElementById('recordMessage').classList.remove('show');
    }

    // Guardar puntuación en el servidor
    saveScore();

    showScreen('gameOverScreen');
}

async function saveScore() {
    try {
        const response = await fetch(CONFIG.API_BASE_URL + 'save-score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: gameState.username,
                score: gameState.score,
                level: gameState.level,
                ballsCount: gameState.ballsCount
            })
        });

        const data = await response.json();

        if (data.success) {
            const rankingMsg = document.getElementById('rankingMessage');
            rankingMsg.textContent = `🏆 Ranking global: #${data.ranking}`;

            if (data.isNewRecord) {
                rankingMsg.textContent += ' - ¡Nuevo récord personal en el servidor!';
            }
        }
    } catch (error) {
        console.error('Error al guardar puntuación:', error);
    }
}

async function loadLeaderboard() {
    const leaderboardList = document.getElementById('leaderboardList');
    leaderboardList.innerHTML = '<div class="loading">Cargando...</div>';

    try {
        const response = await fetch(
            `${CONFIG.API_BASE_URL}leaderboard.php?limit=20&username=${encodeURIComponent(gameState.username)}`
        );
        const data = await response.json();

        if (data.success) {
            leaderboardList.innerHTML = '';

            data.leaderboard.forEach((entry, index) => {
                const item = document.createElement('div');
                item.className = 'leaderboard-item';

                if (index === 0) item.classList.add('top-1');
                else if (index === 1) item.classList.add('top-2');
                else if (index === 2) item.classList.add('top-3');

                item.innerHTML = `
                    <div class="rank">#${entry.ranking}</div>
                    <div class="player-info">
                        <div class="player-name">${escapeHtml(entry.username)}</div>
                        <div class="player-stats">Nivel ${entry.best_level} • ${entry.total_games} partidas</div>
                    </div>
                    <div class="player-score">${entry.best_score}</div>
                `;

                leaderboardList.appendChild(item);
            });

            // Mostrar información del jugador actual
            if (data.playerData) {
                const playerRank = document.getElementById('playerRank');
                playerRank.innerHTML = `
                    <strong>Tu mejor puntuación:</strong> ${data.playerData.best_score} puntos<br>
                    <strong>Tu ranking:</strong> #${data.playerData.ranking}
                `;
            }
        }
    } catch (error) {
        console.error('Error al cargar leaderboard:', error);
        leaderboardList.innerHTML = '<div class="loading">Error al cargar datos</div>';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    initGame();
    setupControls();

    document.getElementById('startButton').addEventListener('click', startGame);

    document.getElementById('leaderboardButton').addEventListener('click', () => {
        showScreen('leaderboardScreen');
        loadLeaderboard();
    });

    document.getElementById('playAgainButton').addEventListener('click', startGame);

    document.getElementById('menuButton').addEventListener('click', () => {
        showScreen('startScreen');
    });

    document.getElementById('backButton').addEventListener('click', () => {
        showScreen('startScreen');
    });

    document.getElementById('usernameInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            startGame();
        }
    });
});
