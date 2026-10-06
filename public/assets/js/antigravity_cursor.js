/**
 * ANTIGRAVITY INTERACTIVE CURSOR & DYNAMIC PARTICLE CONSTELLATION ENGINE
 * Built for Enterprise ERP Landing Home Page
 */
document.addEventListener('DOMContentLoaded', function() {
    // 1. Create Cursor Elements & Spotlight Layer
    const cursorDot = document.createElement('div');
    cursorDot.id = 'agy-cursor-dot';

    const cursorRing = document.createElement('div');
    cursorRing.id = 'agy-cursor-ring';

    const spotlight = document.createElement('div');
    spotlight.id = 'agy-spotlight';

    const canvas = document.createElement('canvas');
    canvas.id = 'antigravity-canvas';

    document.body.appendChild(spotlight);
    document.body.appendChild(canvas);
    document.body.appendChild(cursorRing);
    document.body.appendChild(cursorDot);

    // Mouse Positions
    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;

    let ringX = mouseX;
    let ringY = mouseY;

    // Spotlight & Dual-Cursor Follower Loop
    document.addEventListener('mousemove', function(e) {
        mouseX = e.clientX;
        mouseY = e.clientY;

        cursorDot.style.left = mouseX + 'px';
        cursorDot.style.top = mouseY + 'px';

        document.documentElement.style.setProperty('--mouse-x', mouseX + 'px');
        document.documentElement.style.setProperty('--mouse-y', mouseY + 'px');

        // Spawn interactive trail spark particle
        if (Math.random() < 0.35) {
            spawnTrailParticle(mouseX, mouseY);
        }
    });

    function animateCursorRing() {
        // Smooth lerp following
        ringX += (mouseX - ringX) * 0.18;
        ringY += (mouseY - ringY) * 0.18;

        cursorRing.style.left = ringX + 'px';
        cursorRing.style.top = ringY + 'px';

        requestAnimationFrame(animateCursorRing);
    }
    animateCursorRing();

    // 2. Event Delegation for Hover Detection on All Current & Dynamic Elements
    document.addEventListener('mouseover', function(e) {
        if (e.target.closest('a, button, input, select, textarea, .glass-card, .card, .btn, .list-group-item, tr')) {
            document.body.classList.add('agy-hover');
        }
    });

    document.addEventListener('mouseout', function(e) {
        if (e.target.closest('a, button, input, select, textarea, .glass-card, .card, .btn, .list-group-item, tr')) {
            document.body.classList.remove('agy-hover');
        }
    });

    // 3. 3D Card Tilt Effect on Mouse Movement
    const tiltCards = document.querySelectorAll('.glass-card, .card');
    tiltCards.forEach(card => {
        card.classList.add('tilt-card');
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -8;
            const rotateY = ((x - centerX) / centerX) * 8;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(8px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
        });
    });

    // 4. Antigravity Particle Constellation Canvas Engine
    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = [];
    const trailParticles = [];
    const particleCount = Math.min(Math.floor(window.innerWidth / 16), 80);

    const colors = ['#38bdf8', '#818cf8', '#6366f1', '#a855f7', '#34d399'];

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.8;
            this.vy = (Math.random() - 0.5) * 0.8;
            this.radius = Math.random() * 2.2 + 1;
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.baseAlpha = Math.random() * 0.5 + 0.3;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;

            // Antigravity Magnetic Pull towards Cursor
            const dx = mouseX - this.x;
            const dy = mouseY - this.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < 180) {
                const angle = Math.atan2(dy, dx);
                const force = (180 - dist) / 180;
                this.x += Math.cos(angle) * force * 1.5;
                this.y += Math.sin(angle) * force * 1.5;
            }
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.globalAlpha = this.baseAlpha;
            ctx.shadowBlur = 10;
            ctx.shadowColor = this.color;
            ctx.fill();
            ctx.shadowBlur = 0;
            ctx.globalAlpha = 1.0;
        }
    }

    // Initialize Particle Fleet
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function spawnTrailParticle(px, py) {
        trailParticles.push({
            x: px + (Math.random() - 0.5) * 10,
            y: py + (Math.random() - 0.5) * 10,
            vx: (Math.random() - 0.5) * 1.2,
            vy: -Math.random() * 1.5 - 0.5,
            radius: Math.random() * 2 + 1,
            color: colors[Math.floor(Math.random() * colors.length)],
            life: 1.0,
            decay: Math.random() * 0.03 + 0.02
        });
    }

    function renderCanvas() {
        ctx.clearRect(0, 0, width, height);

        // Draw Inter-Particle & Cursor Constellation Lines
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            // Line to Cursor
            const dx = mouseX - particles[i].x;
            const dy = mouseY - particles[i].y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < 150) {
                ctx.beginPath();
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(mouseX, mouseY);
                ctx.strokeStyle = '#38bdf8';
                ctx.globalAlpha = (1 - dist / 150) * 0.35;
                ctx.lineWidth = 1.0;
                ctx.stroke();
                ctx.globalAlpha = 1.0;
            }

            // Line to Neighbor Particles
            for (let j = i + 1; j < particles.length; j++) {
                const pdx = particles[i].x - particles[j].x;
                const pdy = particles[i].y - particles[j].y;
                const pdist = Math.sqrt(pdx * pdx + pdy * pdy);

                if (pdist < 110) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = particles[i].color;
                    ctx.globalAlpha = (1 - pdist / 110) * 0.22;
                    ctx.lineWidth = 0.8;
                    ctx.stroke();
                    ctx.globalAlpha = 1.0;
                }
            }
        }

        // Render Trail Sparkles
        for (let i = trailParticles.length - 1; i >= 0; i--) {
            const tp = trailParticles[i];
            tp.x += tp.vx;
            tp.y += tp.vy;
            tp.life -= tp.decay;

            if (tp.life <= 0) {
                trailParticles.splice(i, 1);
                continue;
            }

            ctx.beginPath();
            ctx.arc(tp.x, tp.y, tp.radius * tp.life, 0, Math.PI * 2);
            ctx.fillStyle = tp.color;
            ctx.globalAlpha = tp.life * 0.8;
            ctx.shadowBlur = 8;
            ctx.shadowColor = tp.color;
            ctx.fill();
            ctx.shadowBlur = 0;
            ctx.globalAlpha = 1.0;
        }

        requestAnimationFrame(renderCanvas);
    }

    renderCanvas();
});
