<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ScienceBackground from '@/Components/ScienceBackground.vue';

defineProps({
    regions: Object,
});

const goBack = () => window.history.back();
</script>

<template>
    <Head title="Select Region" />

    <div class="regions">
        <ScienceBackground />

        <nav class="rnav">
            <a href="/" class="rnav-brand">
                <img src="/images/dost-logo.png" alt="DOST Logo" class="rnav-logo" />
                <span class="rnav-title">DOST Customer Relation Management System</span>
            </a>
            <button class="btn-ghost" @click="goBack">
                <i class="ri-arrow-left-line me-1"></i> Back
            </button>
        </nav>

        <main class="rmain">
            <header class="rhero">
                <span class="rhero-kicker">Customer Satisfaction Feedback</span>
                <h1 class="rhero-title">
                    Select <span class="gradient">Regional Office</span>
                </h1>
                <p class="rhero-sub">Choose your region to continue to its available services.</p>
                <span class="rhero-count">{{ regions?.length || 0 }} regions available</span>
            </header>

            <div class="region-grid">
                <Link
                    v-for="(region, index) in regions"
                    :key="region.id"
                    :href="'/services/csf/services?region_id=' + region.id"
                    class="region-card"
                    :style="{ animationDelay: (index * 45) + 'ms' }"
                >
                    <div class="region-icon"><i class="ri-map-pin-2-line"></i></div>
                    <div class="region-body">
                        <h3 class="region-short">{{ region.short_name }}</h3>
                        <p class="region-name">{{ region.region_name }}</p>
                    </div>
                    <i class="ri-arrow-right-line region-arrow"></i>
                </Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
.regions {
    position: relative;
    min-height: 100vh;
    color: #e7ecff;
    background: radial-gradient(120% 120% at 50% 0%, #0d1533 0%, #070b1e 55%, #05060f 100%);
    overflow-x: hidden;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Nav */
.rnav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    background: rgba(7, 11, 30, 0.72);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(125, 211, 252, 0.12);
}
.rnav-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; color: #fff; }
.rnav-logo { height: 40px; width: 40px; object-fit: contain; }
.rnav-title { font-weight: 700; font-size: 1.02rem; }
.btn-ghost {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(231, 236, 255, 0.28);
    background: rgba(255, 255, 255, 0.05);
    color: #e7ecff;
    border-radius: 999px;
    padding: 8px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.btn-ghost:hover { background: rgba(255, 255, 255, 0.12); border-color: rgba(231, 236, 255, 0.5); }

/* Main */
.rmain {
    position: relative;
    z-index: 10;
    max-width: 1180px;
    margin: 0 auto;
    padding: 130px 24px 70px;
}

.rhero { text-align: center; margin-bottom: 46px; animation: rise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
.rhero-kicker {
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    font-size: 0.74rem;
    font-weight: 700;
    color: #7dd3fc;
    padding: 6px 15px;
    border: 1px solid rgba(125, 211, 252, 0.35);
    border-radius: 999px;
    background: rgba(125, 211, 252, 0.08);
    margin-bottom: 18px;
}
.rhero-title {
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 800;
    letter-spacing: -1px;
    margin: 0 0 12px;
}
.rhero-title .gradient {
    background: linear-gradient(120deg, #67e8f9 0%, #38bdf8 45%, #818cf8 90%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.rhero-sub { color: #b7c2e8; font-size: 1.05rem; margin: 0 0 14px; }
.rhero-count {
    display: inline-block;
    font-size: 0.82rem;
    color: #8b97c2;
    font-weight: 600;
}

/* Grid */
.region-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    justify-content: center;
    gap: 18px;
}

.region-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px;
    border-radius: 18px;
    text-decoration: none;
    color: inherit;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.09);
    backdrop-filter: blur(10px);
    transition: transform 0.25s ease, border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
    animation: rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.region-card:hover {
    transform: translateY(-6px);
    border-color: rgba(125, 211, 252, 0.55);
    background: rgba(56, 189, 248, 0.08);
    box-shadow: 0 16px 34px rgba(3, 8, 24, 0.5);
}
.region-icon {
    flex-shrink: 0;
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #06122b;
    background: linear-gradient(135deg, #67e8f9, #818cf8);
    transition: transform 0.25s ease;
}
.region-card:hover .region-icon { transform: scale(1.06); }
.region-body { flex: 1; min-width: 0; }
.region-short { font-size: 1.15rem; font-weight: 800; margin: 0 0 2px; color: #fff; }
.region-name {
    font-size: 0.82rem;
    color: #9fabd4;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.region-arrow { color: #6b76a0; font-size: 1.2rem; transition: transform 0.25s ease, color 0.25s ease; }
.region-card:hover .region-arrow { transform: translateX(4px); color: #7dd3fc; }

@keyframes rise {
    from { opacity: 0; transform: translateY(22px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 640px) {
    .rnav-title { display: none; }
    .rmain { padding-top: 110px; }
}

@media (prefers-reduced-motion: reduce) {
    .rhero, .region-card { animation: none; }
    .region-card:hover { transform: none; }
}
</style>
