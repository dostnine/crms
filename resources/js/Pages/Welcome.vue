<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import ScienceBackground from '@/Components/ScienceBackground.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

// Nav gains a solid backdrop once the hero scrolls away.
const scrolled = ref(false);
function onScroll() {
    scrolled.value = window.scrollY > 24;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <Head title="DOST Customer Relation Management System" />

    <div class="landing">
        <!-- Shared 3D scene (canvas + fallback + veil) -->
        <ScienceBackground />

        <!-- Navbar -->
        <nav class="nav" :class="{ 'is-scrolled': scrolled }">
            <a href="/" class="nav-brand">
                <img src="/images/dost-logo.png" alt="DOST Logo" class="nav-logo" />
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
                    Transforming citizen feedback into science-driven insight and a unified
                    platform for measuring service satisfaction across every DOST unit and services.
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
            <img src="/images/dost-logo.png" alt="DOST Logo" class="footer-logo" />
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
    height: 42px;
    width: 42px;
    object-fit: contain;
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
    margin-bottom: 0;
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
.footer-logo { height: 48px; width: 48px; object-fit: contain; margin-bottom: 14px; }
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
}

@media (prefers-reduced-motion: reduce) {
    .hero-inner { animation: none; }
    .wheel { animation: none; }
    .btn:hover, .card:hover { transform: none; }
}
</style>
