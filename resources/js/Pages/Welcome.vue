<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as THREE from 'three';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const canvasEl = ref(null);
const hasWebGL = ref(true);

// Everything the scene owns, so onBeforeUnmount can tear it down cleanly.
let renderer, scene, camera, animationId, resizeObserver;
const disposables = [];
const clock = new THREE.Clock();

// Pointer state, smoothed each frame for a parallax camera.
const pointer = { x: 0, y: 0, tx: 0, ty: 0 };

const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function isWebGLAvailable() {
    try {
        const canvas = document.createElement('canvas');
        return !!(
            window.WebGLRenderingContext &&
            (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
        );
    } catch (e) {
        return false;
    }
}

// A soft round sprite so every particle reads as a glowing dot rather than a hard square.
function makeGlowTexture() {
    const size = 64;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');
    const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
    g.addColorStop(0, 'rgba(255,255,255,1)');
    g.addColorStop(0.25, 'rgba(255,255,255,0.85)');
    g.addColorStop(0.5, 'rgba(255,255,255,0.35)');
    g.addColorStop(1, 'rgba(255,255,255,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, size, size);
    const tex = new THREE.CanvasTexture(canvas);
    tex.needsUpdate = true;
    return tex;
}

const PALETTE = [
    new THREE.Color('#38bdf8'), // sky
    new THREE.Color('#6366f1'), // indigo
    new THREE.Color('#22d3ee'), // cyan
    new THREE.Color('#818cf8'), // violet
    new THREE.Color('#fbbf24'), // amber accent (rare)
];

function pickColor() {
    // Weight the amber accent so it stays a highlight, not a theme.
    const r = Math.random();
    if (r > 0.94) return PALETTE[4];
    return PALETTE[Math.floor(Math.random() * 4)];
}

// ---- Scene pieces -----------------------------------------------------------

// A drifting network of nodes with links drawn between near neighbours: reads as
// a molecule / knowledge graph / data mesh — the DOST "science & connection" idea.
let network;
function buildNetwork(nodeCount, linkDistance, glowTex) {
    const group = new THREE.Group();

    const positions = new Float32Array(nodeCount * 3);
    const velocities = new Float32Array(nodeCount * 3);
    const colors = new Float32Array(nodeCount * 3);
    const radius = 26;

    for (let i = 0; i < nodeCount; i++) {
        // Distribute inside a sphere for a rounded cloud.
        const r = radius * Math.cbrt(Math.random());
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        positions[i * 3] = r * Math.sin(phi) * Math.cos(theta);
        positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
        positions[i * 3 + 2] = r * Math.cos(phi);

        velocities[i * 3] = (Math.random() - 0.5) * 0.6;
        velocities[i * 3 + 1] = (Math.random() - 0.5) * 0.6;
        velocities[i * 3 + 2] = (Math.random() - 0.5) * 0.6;

        const c = pickColor();
        colors[i * 3] = c.r;
        colors[i * 3 + 1] = c.g;
        colors[i * 3 + 2] = c.b;
    }

    const nodeGeo = new THREE.BufferGeometry();
    nodeGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    nodeGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const nodeMat = new THREE.PointsMaterial({
        size: 1.5,
        map: glowTex,
        vertexColors: true,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
    });
    const nodes = new THREE.Points(nodeGeo, nodeMat);
    group.add(nodes);

    // Links: pre-allocate the maximum possible segment buffer, redraw counts per frame.
    const maxLinks = nodeCount * 6;
    const linkPositions = new Float32Array(maxLinks * 2 * 3);
    const linkColors = new Float32Array(maxLinks * 2 * 3);
    const linkGeo = new THREE.BufferGeometry();
    linkGeo.setAttribute('position', new THREE.BufferAttribute(linkPositions, 3));
    linkGeo.setAttribute('color', new THREE.BufferAttribute(linkColors, 3));
    const linkMat = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.35,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    const links = new THREE.LineSegments(linkGeo, linkMat);
    group.add(links);

    disposables.push(nodeGeo, nodeMat, linkGeo, linkMat);

    network = {
        group,
        nodes,
        links,
        positions,
        velocities,
        colors,
        linkPositions,
        linkColors,
        nodeCount,
        linkDistance,
        radius,
    };
    return group;
}

function updateNetwork(dt) {
    const { positions, velocities, colors, nodeCount, linkDistance, radius } = network;

    // Drift nodes and softly reflect them back toward the sphere.
    for (let i = 0; i < nodeCount; i++) {
        const ix = i * 3;
        positions[ix] += velocities[ix] * dt;
        positions[ix + 1] += velocities[ix + 1] * dt;
        positions[ix + 2] += velocities[ix + 2] * dt;

        const d = Math.hypot(positions[ix], positions[ix + 1], positions[ix + 2]);
        if (d > radius) {
            velocities[ix] -= (positions[ix] / d) * 0.08;
            velocities[ix + 1] -= (positions[ix + 1] / d) * 0.08;
            velocities[ix + 2] -= (positions[ix + 2] / d) * 0.08;
        }
    }
    network.nodes.geometry.attributes.position.needsUpdate = true;

    // Rebuild links between near neighbours.
    const lp = network.linkPositions;
    const lc = network.linkColors;
    const maxSegments = lp.length / 6;
    let seg = 0;
    const linkDistSq = linkDistance * linkDistance;

    for (let i = 0; i < nodeCount; i++) {
        const ix = i * 3;
        for (let j = i + 1; j < nodeCount; j++) {
            const jx = j * 3;
            const dx = positions[ix] - positions[jx];
            const dy = positions[ix + 1] - positions[jx + 1];
            const dz = positions[ix + 2] - positions[jx + 2];
            const distSq = dx * dx + dy * dy + dz * dz;
            if (distSq < linkDistSq) {
                if (seg >= maxSegments) break;
                const o = seg * 6;
                lp[o] = positions[ix];
                lp[o + 1] = positions[ix + 1];
                lp[o + 2] = positions[ix + 2];
                lp[o + 3] = positions[jx];
                lp[o + 4] = positions[jx + 1];
                lp[o + 5] = positions[jx + 2];

                // Fade each link toward transparent as it approaches the cutoff.
                const t = 1 - Math.sqrt(distSq) / linkDistance;
                lc[o] = colors[ix] * t;
                lc[o + 1] = colors[ix + 1] * t;
                lc[o + 2] = colors[ix + 2] * t;
                lc[o + 3] = colors[jx] * t;
                lc[o + 4] = colors[jx + 1] * t;
                lc[o + 5] = colors[jx + 2] * t;
                seg++;
            }
        }
    }

    network.links.geometry.setDrawRange(0, seg * 2);
    network.links.geometry.attributes.position.needsUpdate = true;
    network.links.geometry.attributes.color.needsUpdate = true;
}

// A far ambient starfield for depth parallax.
let starfield;
function buildStarfield(count, glowTex) {
    const positions = new Float32Array(count * 3);
    const colors = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 220;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 220;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 220 - 40;
        const c = pickColor();
        const dim = 0.4 + Math.random() * 0.4;
        colors[i * 3] = c.r * dim;
        colors[i * 3 + 1] = c.g * dim;
        colors[i * 3 + 2] = c.b * dim;
    }
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));
    const mat = new THREE.PointsMaterial({
        size: 0.7,
        map: glowTex,
        vertexColors: true,
        transparent: true,
        opacity: 0.8,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
    });
    disposables.push(geo, mat);
    starfield = new THREE.Points(geo, mat);
    return starfield;
}

// A glowing wireframe core at the centre — the "engine" of the scene.
let core;
function buildCore() {
    const group = new THREE.Group();

    const geo = new THREE.IcosahedronGeometry(6, 1);
    const wire = new THREE.WireframeGeometry(geo);
    const mat = new THREE.LineBasicMaterial({
        color: new THREE.Color('#7dd3fc'),
        transparent: true,
        opacity: 0.55,
        blending: THREE.AdditiveBlending,
        depthWrite: false,
    });
    const lines = new THREE.LineSegments(wire, mat);
    group.add(lines);

    const innerGeo = new THREE.IcosahedronGeometry(3.4, 0);
    const innerMat = new THREE.MeshBasicMaterial({
        color: new THREE.Color('#1e3a8a'),
        transparent: true,
        opacity: 0.35,
        blending: THREE.AdditiveBlending,
        depthWrite: false,
    });
    const innerMesh = new THREE.Mesh(innerGeo, innerMat);
    group.add(innerMesh);

    disposables.push(geo, wire, mat, innerGeo, innerMat);
    core = { group, lines, innerMesh };
    return group;
}

// ---- Lifecycle --------------------------------------------------------------

function init() {
    const el = canvasEl.value;
    const width = el.clientWidth;
    const height = el.clientHeight;

    scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2('#070b1e', 0.011);

    camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 400);
    camera.position.set(0, 0, 62);

    renderer = new THREE.WebGLRenderer({ canvas: el, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(width, height, false);
    renderer.setClearColor(0x000000, 0);

    const glowTex = makeGlowTexture();
    disposables.push(glowTex);

    // Scale scene complexity to the device so phones stay smooth.
    const isMobile = window.innerWidth < 768;
    const nodeCount = isMobile ? 60 : 110;
    const linkDistance = isMobile ? 9 : 8;
    const starCount = isMobile ? 700 : 1600;

    scene.add(buildStarfield(starCount, glowTex));
    scene.add(buildNetwork(nodeCount, linkDistance, glowTex));

    const coreGroup = buildCore();
    // Sit the core low and back so it frames the headline without fighting the
    // subtitle copy for legibility.
    coreGroup.position.set(0, -9, -6);
    scene.add(coreGroup);

    if (prefersReducedMotion) {
        // Draw one composed frame and stop — no motion for users who opt out.
        updateNetwork(0);
        renderer.render(scene, camera);
        return;
    }

    animate();
}

function animate() {
    animationId = requestAnimationFrame(animate);
    const dt = Math.min(clock.getDelta(), 0.05);
    const t = clock.elapsedTime;

    updateNetwork(dt);

    if (network) {
        network.group.rotation.y += dt * 0.06;
        network.group.rotation.x = Math.sin(t * 0.15) * 0.12;
    }
    if (starfield) {
        starfield.rotation.y += dt * 0.01;
    }
    if (core) {
        core.lines.rotation.y -= dt * 0.2;
        core.lines.rotation.x += dt * 0.08;
        const pulse = 1 + Math.sin(t * 1.6) * 0.06;
        core.innerMesh.scale.setScalar(pulse);
        core.innerMesh.material.opacity = 0.28 + (Math.sin(t * 1.6) + 1) * 0.12;
    }

    // Smooth the camera toward the pointer for gentle parallax.
    pointer.x += (pointer.tx - pointer.x) * 0.04;
    pointer.y += (pointer.ty - pointer.y) * 0.04;
    camera.position.x = pointer.x * 10;
    camera.position.y = pointer.y * 6;
    camera.lookAt(scene.position);

    renderer.render(scene, camera);
}

function onResize() {
    if (!renderer || !canvasEl.value) return;
    const el = canvasEl.value;
    const width = el.clientWidth;
    const height = el.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height, false);
    if (prefersReducedMotion) renderer.render(scene, camera);
}

function onPointerMove(e) {
    pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
    pointer.ty = -((e.clientY / window.innerHeight) * 2 - 1);
}

const scrolled = ref(false);
function onScroll() {
    scrolled.value = window.scrollY > 24;
}

onMounted(() => {
    if (!isWebGLAvailable()) {
        hasWebGL.value = false;
        return;
    }
    init();
    resizeObserver = new ResizeObserver(onResize);
    resizeObserver.observe(canvasEl.value);
    window.addEventListener('pointermove', onPointerMove, { passive: true });
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    if (animationId) cancelAnimationFrame(animationId);
    if (resizeObserver) resizeObserver.disconnect();
    window.removeEventListener('pointermove', onPointerMove);
    window.removeEventListener('scroll', onScroll);
    disposables.forEach((d) => d.dispose && d.dispose());
    if (renderer) renderer.dispose();
    scene = camera = renderer = network = starfield = core = null;
});
</script>

<template>
    <Head title="DOST Customer Relation Management System" />

    <div class="landing">
        <!-- 3D canvas layer -->
        <canvas ref="canvasEl" class="scene-canvas" :class="{ 'is-hidden': !hasWebGL }"></canvas>
        <!-- Static fallback if WebGL is unavailable -->
        <div class="scene-fallback" :class="{ 'is-visible': !hasWebGL }"></div>
        <!-- Readability veil over the scene -->
        <div class="scene-veil"></div>

        <!-- Navbar -->
        <nav class="nav" :class="{ 'is-scrolled': scrolled }">
            <a href="/" class="nav-brand">
                <img src="/images/dost-logo.jpg" alt="DOST Logo" class="nav-logo" />
                <span class="nav-title">Department of Science and Technology</span>
            </a>
            <a href="/services/csf/regions" class="btn btn-ghost">Get Started</a>
        </nav>

        <!-- Hero -->
        <header class="hero">
            <div class="hero-inner">
                <span class="hero-kicker">Region IX · Zamboanga Peninsula</span>
                <h1 class="hero-title">
                    <span class="line">Customer Relation</span>
                    <span class="line gradient">Management System</span>
                </h1>
                <p class="hero-sub">
                    Transforming citizen feedback into science-driven insight — a unified
                    platform for measuring service satisfaction across every DOST unit.
                </p>
                <div class="hero-actions">
                    <a href="/services/csf/regions" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Start Your Survey
                    </a>
                    <a href="#about" class="btn btn-outline">Learn more</a>
                </div>

                <dl class="hero-stats">
                    <div class="stat">
                        <dt>16</dt>
                        <dd>Regions</dd>
                    </div>
                    <div class="stat">
                        <dt>8,500+</dt>
                        <dd>Responses</dd>
                    </div>
                    <div class="stat">
                        <dt>Real-time</dt>
                        <dd>CSI &amp; NPS</dd>
                    </div>
                </dl>
            </div>

            <a href="#about" class="scroll-cue" aria-label="Scroll to content">
                <span class="mouse"><span class="wheel"></span></span>
            </a>
        </header>

        <!-- Capabilities -->
        <section id="about" class="section">
            <div class="section-head">
                <span class="section-kicker">What it does</span>
                <h2>Built for evidence-based public service</h2>
                <p>
                    Every scanned survey flows into live dashboards, giving each office a
                    faithful picture of how citizens experience its services.
                </p>
            </div>

            <div class="cards">
                <article class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l4-4 3 3 5-6" />
                        </svg>
                    </div>
                    <h3>Live analytics</h3>
                    <p>Customer Satisfaction Index and Net Promoter Score computed per month, quarter and year.</p>
                </article>
                <article class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="4" width="16" height="16" rx="2" />
                            <path stroke-linecap="round" d="M8 4v4M16 4v4M4 10h16" />
                        </svg>
                    </div>
                    <h3>QR-based collection</h3>
                    <p>Each unit generates a unique QR that opens the right survey — no manual routing.</p>
                </article>
                <article class="card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z" />
                        </svg>
                    </div>
                    <h3>Privacy first</h3>
                    <p>Feedback is collected solely for documentation and never published to any public platform.</p>
                </article>
            </div>

            <div class="section-cta">
                <a href="/services/csf/regions" class="btn btn-primary">Begin a survey</a>
            </div>
        </section>

        <footer class="footer">
            <img src="/images/dost-logo.jpg" alt="DOST Logo" class="footer-logo" />
            <p>Department of Science and Technology — Customer Relation Management System</p>
            <span class="footer-note">For authorized use in citizen satisfaction measurement.</span>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    position: relative;
    min-height: 100vh;
    color: #e7ecff;
    background: radial-gradient(120% 120% at 50% 0%, #0d1533 0%, #070b1e 55%, #05060f 100%);
    overflow-x: hidden;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Scene layers */
.scene-canvas {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100vh;
    z-index: 0;
    display: block;
}
.scene-canvas.is-hidden { display: none; }
.scene-fallback {
    position: fixed;
    inset: 0;
    z-index: 0;
    display: none;
    background:
        radial-gradient(60% 60% at 20% 20%, rgba(56, 189, 248, 0.25), transparent 60%),
        radial-gradient(50% 50% at 80% 30%, rgba(99, 102, 241, 0.28), transparent 60%),
        radial-gradient(70% 70% at 50% 90%, rgba(34, 211, 238, 0.18), transparent 60%);
}
.scene-fallback.is-visible { display: block; }
.scene-veil {
    position: fixed;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background: radial-gradient(120% 90% at 50% 15%, transparent 40%, rgba(5, 6, 15, 0.55) 100%);
}

/* Navbar */
.nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 28px;
    transition: background 0.4s ease, backdrop-filter 0.4s ease, padding 0.4s ease;
}
.nav.is-scrolled {
    background: rgba(7, 11, 30, 0.72);
    backdrop-filter: blur(14px);
    padding: 10px 28px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
}
.nav-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #fff;
}
.nav-logo {
    height: 40px;
    width: 40px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.15);
}
.nav-title {
    font-weight: 700;
    font-size: 1.05rem;
    letter-spacing: 0.2px;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.98rem;
    padding: 13px 26px;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease, border-color 0.25s ease;
    white-space: nowrap;
}
.btn:hover { transform: translateY(-2px); }
.btn-primary {
    color: #06122b;
    background: linear-gradient(135deg, #67e8f9 0%, #38bdf8 45%, #818cf8 100%);
    box-shadow: 0 10px 30px rgba(56, 189, 248, 0.35);
}
.btn-primary:hover { box-shadow: 0 16px 44px rgba(56, 189, 248, 0.5); }
.btn-outline {
    color: #e7ecff;
    border-color: rgba(231, 236, 255, 0.35);
    background: rgba(255, 255, 255, 0.04);
}
.btn-outline:hover { border-color: rgba(231, 236, 255, 0.7); background: rgba(255, 255, 255, 0.09); }
.btn-ghost {
    color: #e7ecff;
    border-color: rgba(231, 236, 255, 0.3);
    padding: 10px 22px;
    background: rgba(255, 255, 255, 0.05);
}
.btn-ghost:hover { background: rgba(255, 255, 255, 0.12); }

/* Hero */
.hero {
    position: relative;
    z-index: 10;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 120px 24px 80px;
}
.hero-inner {
    max-width: 860px;
    animation: rise 1s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.hero-kicker {
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #7dd3fc;
    padding: 7px 16px;
    border: 1px solid rgba(125, 211, 252, 0.35);
    border-radius: 999px;
    background: rgba(125, 211, 252, 0.08);
    margin-bottom: 26px;
}
.hero-title {
    font-size: clamp(2.6rem, 7vw, 5.2rem);
    line-height: 1.02;
    font-weight: 800;
    letter-spacing: -1.5px;
    margin: 0 0 22px;
    text-shadow: 0 2px 30px rgba(5, 8, 20, 0.75);
}
.hero-title .line { display: block; }
.hero-title .gradient {
    background: linear-gradient(120deg, #67e8f9 0%, #38bdf8 40%, #818cf8 80%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.hero-sub {
    font-size: clamp(1.02rem, 2.2vw, 1.28rem);
    line-height: 1.6;
    color: #c6d0ee;
    max-width: 620px;
    margin: 0 auto 38px;
    text-shadow: 0 2px 24px rgba(5, 8, 20, 0.9);
}
.hero-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 56px;
}
.hero-stats {
    display: flex;
    gap: 48px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 0;
}
.hero-stats .stat { text-align: center; }
.hero-stats dt {
    font-size: clamp(1.6rem, 4vw, 2.3rem);
    font-weight: 800;
    background: linear-gradient(120deg, #e7ecff, #7dd3fc);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.hero-stats dd {
    margin: 4px 0 0;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #8b97c2;
}

/* Scroll cue */
.scroll-cue {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
}
.mouse {
    display: block;
    width: 26px;
    height: 42px;
    border: 2px solid rgba(231, 236, 255, 0.5);
    border-radius: 14px;
    position: relative;
}
.wheel {
    position: absolute;
    top: 8px;
    left: 50%;
    width: 4px;
    height: 8px;
    margin-left: -2px;
    border-radius: 2px;
    background: #7dd3fc;
    animation: wheel 1.6s infinite;
}

/* Section */
.section {
    position: relative;
    z-index: 10;
    max-width: 1120px;
    margin: 0 auto;
    padding: 100px 24px;
    background: linear-gradient(180deg, transparent, rgba(7, 11, 30, 0.6) 12%, rgba(7, 11, 30, 0.85));
}
.section-head { text-align: center; max-width: 640px; margin: 0 auto 56px; }
.section-kicker {
    text-transform: uppercase;
    letter-spacing: 3px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #7dd3fc;
}
.section-head h2 {
    font-size: clamp(1.8rem, 4vw, 2.7rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    margin: 14px 0 16px;
}
.section-head p { color: #b7c2e8; line-height: 1.6; font-size: 1.05rem; }

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 22px;
}
.card {
    padding: 32px 28px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.09);
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease, border-color 0.3s ease, background 0.3s ease;
}
.card:hover {
    transform: translateY(-8px);
    border-color: rgba(125, 211, 252, 0.5);
    background: rgba(56, 189, 248, 0.07);
}
.card-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #06122b;
    background: linear-gradient(135deg, #67e8f9, #818cf8);
}
.card-icon svg { width: 26px; height: 26px; }
.card h3 { font-size: 1.25rem; font-weight: 700; margin: 0 0 10px; }
.card p { color: #a9b4d9; line-height: 1.55; margin: 0; font-size: 0.98rem; }

.section-cta { text-align: center; margin-top: 52px; }

/* Footer */
.footer {
    position: relative;
    z-index: 10;
    text-align: center;
    padding: 48px 24px 60px;
    background: rgba(5, 6, 15, 0.9);
    border-top: 1px solid rgba(255, 255, 255, 0.07);
}
.footer-logo { height: 46px; width: 46px; border-radius: 50%; object-fit: cover; margin-bottom: 14px; }
.footer p { margin: 0 0 6px; font-weight: 600; color: #d7deff; }
.footer-note { font-size: 0.85rem; color: #7d88b0; }

/* Animations */
@keyframes rise {
    from { opacity: 0; transform: translateY(28px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes wheel {
    0% { opacity: 0; transform: translateY(0); }
    30% { opacity: 1; }
    100% { opacity: 0; transform: translateY(14px); }
}

@media (max-width: 640px) {
    .nav-title { display: none; }
    .hero-stats { gap: 30px; }
}

@media (prefers-reduced-motion: reduce) {
    .hero-inner { animation: none; }
    .wheel { animation: none; }
    .btn:hover, .card:hover { transform: none; }
}
</style>
