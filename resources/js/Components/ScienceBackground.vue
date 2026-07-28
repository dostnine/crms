<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as THREE from 'three';

// A drifting molecular / knowledge-graph network with an ambient starfield and a
// glowing wireframe core, plus gentle pointer parallax. Shared by the landing and
// auth pages so they read as one product. Renders three fixed layers behind page
// content; the host page owns everything above z-index 2.

const canvasEl = ref(null);
const hasWebGL = ref(true);

let renderer, scene, camera, animationId, resizeObserver;
const disposables = [];
const clock = new THREE.Clock();
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
    const r = Math.random();
    if (r > 0.94) return PALETTE[4];
    return PALETTE[Math.floor(Math.random() * 4)];
}

let network;
function buildNetwork(nodeCount, linkDistance, glowTex) {
    const group = new THREE.Group();

    const positions = new Float32Array(nodeCount * 3);
    const velocities = new Float32Array(nodeCount * 3);
    const colors = new Float32Array(nodeCount * 3);
    const radius = 26;

    for (let i = 0; i < nodeCount; i++) {
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
        group, nodes, links, positions, velocities, colors,
        linkPositions, linkColors, nodeCount, linkDistance, radius,
    };
    return group;
}

function updateNetwork(dt) {
    const { positions, velocities, colors, nodeCount, linkDistance, radius } = network;

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

    const isMobile = window.innerWidth < 768;
    const nodeCount = isMobile ? 60 : 110;
    const linkDistance = isMobile ? 9 : 8;
    const starCount = isMobile ? 700 : 1600;

    scene.add(buildStarfield(starCount, glowTex));
    scene.add(buildNetwork(nodeCount, linkDistance, glowTex));

    const coreGroup = buildCore();
    coreGroup.position.set(0, -9, -6);
    scene.add(coreGroup);

    if (prefersReducedMotion) {
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

onMounted(() => {
    if (!isWebGLAvailable()) {
        hasWebGL.value = false;
        return;
    }
    init();
    resizeObserver = new ResizeObserver(onResize);
    resizeObserver.observe(canvasEl.value);
    window.addEventListener('pointermove', onPointerMove, { passive: true });
});

onBeforeUnmount(() => {
    if (animationId) cancelAnimationFrame(animationId);
    if (resizeObserver) resizeObserver.disconnect();
    window.removeEventListener('pointermove', onPointerMove);
    disposables.forEach((d) => d.dispose && d.dispose());
    if (renderer) renderer.dispose();
    scene = camera = renderer = network = starfield = core = null;
});
</script>

<template>
    <canvas ref="canvasEl" class="scene-canvas" :class="{ 'is-hidden': !hasWebGL }"></canvas>
    <div class="scene-fallback" :class="{ 'is-visible': !hasWebGL }"></div>
    <div class="scene-veil"></div>
</template>

<style scoped>
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
</style>
