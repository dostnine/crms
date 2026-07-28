<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ScienceBackground from '@/Components/ScienceBackground.vue';

defineProps({
    region_id: Number,
    region: Object,
    service_id: Number,
    unit_id: Number,
    sub_unit_id: Number,
    sub_unit: Object,
    types: Object,
});

// Give each card a distinct icon by cycling a themed set.
const typeIcons = [
    'ri-car-line', 'ri-roadster-line', 'ri-steering-2-line', 'ri-signal-tower-line',
    'ri-road-map-line', 'ri-traffic-light-line', 'ri-e-bike-2-line', 'ri-taxi-line',
    'ri-bus-2-line', 'ri-motorbike-line',
];
const iconFor = (i) => typeIcons[i % typeIcons.length];

// Percent-encode the type name: values like "CJB (PSTO-ZS)" contain spaces and
// would otherwise be truncated in the query string.
const typeHref = (region_id, service_id, unit_id, sub_unit_id, type_name) =>
    `/services/csf?region_id=${region_id}` +
    `&service_id=${service_id}` +
    `&unit_id=${unit_id}` +
    `&sub_unit_id=${sub_unit_id}` +
    `&sub_unit_type=${encodeURIComponent(type_name)}`;

const goBack = () => window.history.back();
</script>

<template>
    <Head title="Select Type" />

    <div class="flow">
        <ScienceBackground />

        <nav class="rnav">
            <a href="/" class="rnav-brand">
                <img src="/images/dost-logo.png" alt="DOST Logo" class="rnav-logo" />
                <span class="rnav-title">
                    DOST <span v-if="region">{{ region.code }}</span> Customer Relation Management System
                </span>
            </a>
            <button class="btn-ghost" @click="goBack">
                <i class="ri-arrow-left-line me-1"></i> Back
            </button>
        </nav>

        <main class="rmain">
            <header class="rhero">
                <span class="rhero-kicker">
                    <span v-if="region">{{ region.code }} · </span>{{ sub_unit?.sub_unit_name || 'Unit Types' }}
                </span>
                <h1 class="rhero-title">Select a <span class="gradient">Type</span></h1>
                <p class="rhero-sub">Choose a type to continue to your feedback form.</p>
                <span class="rhero-count">{{ types?.length || 0 }} types available</span>
            </header>

            <div class="card-grid">
                <Link
                    v-for="(type, index) in types"
                    :key="type.id"
                    :href="typeHref(region_id, service_id, unit_id, sub_unit_id, type.type_name)"
                    class="flow-card"
                    :style="{ animationDelay: (index * 45) + 'ms' }"
                >
                    <div class="flow-icon"><i :class="iconFor(index)"></i></div>
                    <h3 class="flow-name">{{ type.type_name }}</h3>
                    <span class="flow-cta">Continue <i class="ri-arrow-right-line"></i></span>
                </Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
.flow {
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
.rhero-count { display: inline-block; font-size: 0.82rem; color: #8b97c2; font-weight: 600; }

/* Grid (centered) */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 260px));
    justify-content: center;
    gap: 18px;
}

.flow-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    padding: 30px 22px;
    border-radius: 18px;
    text-decoration: none;
    color: inherit;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.09);
    backdrop-filter: blur(10px);
    transition: transform 0.25s ease, border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
    animation: rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.flow-card:hover {
    transform: translateY(-6px);
    border-color: rgba(125, 211, 252, 0.55);
    background: rgba(56, 189, 248, 0.08);
    box-shadow: 0 16px 34px rgba(3, 8, 24, 0.5);
}
.flow-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    color: #06122b;
    background: linear-gradient(135deg, #67e8f9, #818cf8);
    transition: transform 0.25s ease;
}
.flow-card:hover .flow-icon { transform: scale(1.07); }
.flow-name { font-size: 1.02rem; font-weight: 800; color: #fff; margin: 0; line-height: 1.35; }
.flow-cta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #7dd3fc;
    margin-top: auto;
}
.flow-cta i { transition: transform 0.25s ease; }
.flow-card:hover .flow-cta i { transform: translateX(4px); }

@keyframes rise {
    from { opacity: 0; transform: translateY(22px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 640px) {
    .rnav-title { display: none; }
    .rmain { padding-top: 110px; }
}

@media (prefers-reduced-motion: reduce) {
    .rhero, .flow-card { animation: none; }
    .flow-card:hover { transform: none; }
}
</style>
