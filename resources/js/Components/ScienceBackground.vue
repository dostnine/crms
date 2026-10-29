<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// A drifting molecular / knowledge-graph network with an ambient starfield and a
// glowing wireframe core, plus gentle pointer parallax. Shared by the landing and
// auth pages so they read as one product. Renders three fixed layers behind page
// content; the host page owns everything above z-index 2.
//
// The scene itself is in scienceScene.js, with three.js: together most of the
// weight of these pages' scripts (some 450 KB, more than the rest of the app),
// and nothing on the pages needs them to be read or used. So they are fetched
// once the page is up and the browser has a moment, and the scene fades in
// when it is ready, instead of the page waiting for it.

const canvasEl = ref(null);
const hasWebGL = ref(true);
const ready = ref(false);

let sceneNow, resizeObserver;
let gone = false;

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

// once the browser has nothing more pressing to do (or after 1.5 s at most)
const whenIdle = () => new Promise((resolve) => {
    if (window.requestIdleCallback) window.requestIdleCallback(resolve, { timeout: 1500 });
    else setTimeout(resolve, 300);
});

function onResize() {
    if (sceneNow) sceneNow.resize();
}

function onPointerMove(e) {
    if (sceneNow) sceneNow.point((e.clientX / window.innerWidth) * 2 - 1, -((e.clientY / window.innerHeight) * 2 - 1));
}

onMounted(async () => {
    if (!isWebGLAvailable()) {
        hasWebGL.value = false;
        return;
    }
    await whenIdle();
    // (the page may have been left, or have switched to its light theme, meanwhile)
    if (gone) return;
    let scenes;
    try {
        scenes = await import('./scienceScene.js');
    } catch (e) {
        // (it could not be fetched: the still background instead)
        hasWebGL.value = false;
        return;
    }
    // the page may have been left while it came
    if (gone || !canvasEl.value) return;
    sceneNow = scenes.startScene(canvasEl.value, { reducedMotion: prefersReducedMotion });
    ready.value = true;
    resizeObserver = new ResizeObserver(onResize);
    resizeObserver.observe(canvasEl.value);
    window.addEventListener('pointermove', onPointerMove, { passive: true });
});

onBeforeUnmount(() => {
    gone = true;
    if (resizeObserver) resizeObserver.disconnect();
    window.removeEventListener('pointermove', onPointerMove);
    if (sceneNow) sceneNow.dispose();
    sceneNow = null;
});
</script>

<template>
    <canvas ref="canvasEl" class="scene-canvas" :class="{ 'is-hidden': !hasWebGL, 'is-ready': ready }"></canvas>
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
    /* (it fades in once the scene is drawn) */
    opacity: 0;
    transition: opacity 0.8s ease;
}
.scene-canvas.is-ready { opacity: 1; }
.scene-canvas.is-hidden { display: none; }
@media (prefers-reduced-motion: reduce) {
    .scene-canvas { transition: none; }
}
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
