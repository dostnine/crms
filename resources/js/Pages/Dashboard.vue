<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    module_counts: {
        type: Object,
        default: () => ({}),
    },
    distribution: {
        type: Object,
        default: () => ({}),
    },
});

const distributionStops = computed(() => {
    const vs = Number(props.distribution?.very_satisfied?.pct || 0);
    const s = Number(props.distribution?.satisfied?.pct || 0);
    const n = Number(props.distribution?.neutral?.pct || 0);
    const d = Number(props.distribution?.dissatisfied?.pct || 0);
    const stop1 = vs;
    const stop2 = vs + s;
    const stop3 = vs + s + n;
    const stop4 = vs + s + n + d;
    return {
        stop1: stop1.toFixed(2),
        stop2: stop2.toFixed(2),
        stop3: stop3.toFixed(2),
        stop4: stop4.toFixed(2),
    };
});

const quickStats = [
    { label: 'Total Surveys', key: 'total_surveys', icon: 'ri-bar-chart-line', tone: 'stat-blue', suffix: '' },
    { label: 'Active Users', key: 'active_users', icon: 'ri-group-line', tone: 'stat-teal', suffix: '' },
    { label: 'Satisfaction Rate', key: 'satisfaction_rate', icon: 'ri-star-line', tone: 'stat-green', suffix: '%' },
    { label: 'Pending Reviews', key: 'pending_reviews', icon: 'ri-time-line', tone: 'stat-amber', suffix: '' },
];

const modules = [
    {
        title: 'User Accounts',
        description: 'Manage user accounts and permissions',
        href: '/accounts',
        icon: 'ri-user-line',
        badgeKey: 'users',
        badgeSuffix: 'Users',
        tone: 'tile-blue',
    },
    {
        title: 'Service Units',
        description: 'Configure service units and departments',
        href: '/service_units',
        icon: 'ri-building-line',
        badgeKey: 'units',
        badgeSuffix: 'Units',
        tone: 'tile-green',
    },
    {
        title: 'Regions',
        description: 'Manage regional offices and locations',
        href: '/regions',
        icon: 'ri-map-pin-line',
        badgeKey: 'regions',
        badgeSuffix: 'Regions',
        tone: 'tile-cyan',
    },
    {
        title: 'PSTOs',
        description: 'Handle PSTO configurations and operations',
        href: '/pstos',
        icon: 'ri-store-line',
        badgeKey: 'pstos',
        badgeSuffix: 'PSTOs',
        tone: 'tile-coral',
    },
];
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Dashboard</h2>
                <p class="page-heading-subtitle mb-0">System snapshot and quick access to core modules.</p>
            </div>
        </template>

        <div class="dashboard-page py-4">
            <div class="container-fluid px-3 px-md-4" style="max-width: 1440px;">
                <section class="hero-card mb-4">
                    <div class="hero-content">
                        <div>
                            <p class="hero-kicker mb-1">CRMS CSF</p>
                            <h1 class="hero-title mb-1">Welcome back, {{ $page.props.auth.user.name }}!</h1>
                            <p class="hero-text mb-0">Track system activity, monitor satisfaction, and open management modules quickly.</p>
                        </div>
                        <div class="hero-icon">
                            <i class="ri-dashboard-line"></i>
                        </div>
                    </div>
                </section>

                <section class="mb-4">
                    <div class="row g-3">
                        <div v-for="item in quickStats" :key="item.label" class="col-12 col-sm-6 col-xl-3">
                            <article class="stat-card" :class="item.tone">
                                <div>
                                    <p class="stat-label mb-1">{{ item.label }}</p>
                                    <h3 class="stat-value mb-0">{{ props.stats?.[item.key] ?? 0 }}{{ item.suffix }}</h3>
                                </div>
                                <i class="stat-icon" :class="item.icon"></i>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="row g-3 g-lg-4">
                    <div class="col-12 col-lg-8">
                        <div class="panel-card h-100">
                            <div class="panel-head">
                                <h3 class="panel-title mb-0">Management Modules</h3>
                            </div>
                            <div class="panel-body">
                                <div class="row g-3">
                                    <div v-for="module in modules" :key="module.href" class="col-12 col-sm-6">
                                        <Link :href="module.href" class="text-decoration-none">
                                            <article class="module-tile" :class="module.tone">
                                                <div class="module-icon">
                                                    <i :class="module.icon"></i>
                                                </div>
                                                <div class="module-main">
                                                    <h4 class="module-title mb-1">{{ module.title }}</h4>
                                                    <p class="module-desc mb-2">{{ module.description }}</p>
                                                    <span class="module-badge">{{ props.module_counts?.[module.badgeKey] ?? 0 }} {{ module.badgeSuffix }}</span>
                                                </div>
                                                <i class="ri-arrow-right-up-line module-arrow"></i>
                                            </article>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="panel-card h-100">
                            <div class="panel-head">
                                <h3 class="panel-title mb-0">Satisfaction Mix</h3>
                            </div>
                            <div class="panel-body">
                                <div class="mix-wrap mb-3">
                                    <div class="mix-pie" :style="{
                                        background: `conic-gradient(
                                            #2f9a67 0% ${distributionStops.stop1}%,
                                            #2f66b3 ${distributionStops.stop1}% ${distributionStops.stop2}%,
                                            #c58a2f ${distributionStops.stop2}% ${distributionStops.stop3}%,
                                            #bf4d5c ${distributionStops.stop3}% ${distributionStops.stop4}%
                                        )`
                                    }"></div>
                                    <div class="mix-center">
                                        <div class="mix-total">{{ props.distribution?.total_ratings ?? 0 }}</div>
                                        <div class="mix-caption">Total Ratings</div>
                                    </div>
                                </div>
                                <div class="mix-list">
                                    <div class="mix-item"><span class="dot dot-vs"></span>Very Satisfied <strong>{{ props.distribution?.very_satisfied?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="dot dot-s"></span>Satisfied <strong>{{ props.distribution?.satisfied?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="dot dot-n"></span>Neutral <strong>{{ props.distribution?.neutral?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="dot dot-d"></span>Dissatisfied <strong>{{ props.distribution?.dissatisfied?.pct ?? 0 }}%</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard-page {
    background: linear-gradient(135deg, #f5f9ff 0%, #edf3fb 100%);
    min-height: 100vh;
}

.page-heading-title {
    margin: 0;
    color: #12243a;
    font-size: 1.25rem;
    font-weight: 700;
}

.page-heading-subtitle {
    color: #5b7088;
    font-size: 0.9rem;
}

.hero-card {
    border: 1px solid #d9e7f7;
    border-radius: 16px;
    background:
        radial-gradient(circle at top right, rgba(71, 153, 233, 0.3) 0, rgba(71, 153, 233, 0) 45%),
        linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
}

.hero-content {
    padding: 20px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.hero-kicker {
    font-size: 0.75rem;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #3f6c9e;
    font-weight: 700;
}

.hero-title {
    font-size: 1.35rem;
    color: #12243a;
    font-weight: 800;
}

.hero-text {
    color: #38506b;
    font-size: 0.92rem;
}

.hero-icon i {
    font-size: 2.2rem;
    color: #2f5c90;
}

.stat-card {
    border: 1px solid #d5e4f7;
    background: #fff;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 3px 10px rgba(21, 59, 112, 0.08);
}

.stat-label {
    font-size: 0.78rem;
    color: #657e99;
    font-weight: 700;
}

.stat-value {
    font-size: 1.15rem;
    color: #173c66;
    font-weight: 800;
}

.stat-icon {
    font-size: 1.4rem;
}

.stat-blue .stat-icon { color: #2e6fc8; }
.stat-teal .stat-icon { color: #228c9d; }
.stat-green .stat-icon { color: #2e9367; }
.stat-amber .stat-icon { color: #b8782e; }

.panel-card {
    border: 1px solid #d8e5f5;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 8px 24px rgba(21, 59, 112, 0.08);
}

.panel-head {
    padding: 12px 16px;
    background: linear-gradient(90deg, #1b365d, #2a568f);
}

.panel-title {
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
}

.panel-body {
    padding: 14px;
}

.module-tile {
    display: grid;
    grid-template-columns: 44px 1fr 18px;
    gap: 10px;
    align-items: center;
    border: 1px solid #d7e4f5;
    border-radius: 12px;
    padding: 12px;
    background: #fff;
    transition: all 0.2s ease;
}

.module-tile:hover {
    border-color: #b8cee8;
    box-shadow: 0 8px 18px rgba(21, 59, 112, 0.12);
    transform: translateY(-2px);
}

.module-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.module-title {
    font-size: 0.95rem;
    color: #183d67;
    font-weight: 800;
}

.module-desc {
    font-size: 0.8rem;
    color: #607891;
}

.module-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #305982;
    background: #eaf2ff;
    border: 1px solid #cfe0f5;
    border-radius: 999px;
    padding: 2px 8px;
}

.module-arrow {
    color: #6b84a0;
}

.tile-blue .module-icon { background: #eaf1ff; color: #2f66b3; }
.tile-green .module-icon { background: #e8f9f1; color: #1f9d65; }
.tile-cyan .module-icon { background: #eaf8ff; color: #1484ba; }
.tile-coral .module-icon { background: #fff0ec; color: #d35a49; }

.mix-wrap {
    position: relative;
    width: 170px;
    height: 170px;
    margin: 0 auto;
}

.mix-pie {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: conic-gradient(#2f9a67 0% 100%);
    border: 1px solid #c8d9ef;
}

.mix-center {
    position: absolute;
    inset: 30% 30%;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #dce8f7;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.mix-total {
    font-size: 0.9rem;
    font-weight: 800;
    color: #1d436f;
}

.mix-caption {
    font-size: 0.68rem;
    color: #667f99;
}

.mix-list {
    display: grid;
    gap: 6px;
}

.mix-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    color: #365272;
    border: 1px solid #d9e6f6;
    border-radius: 8px;
    padding: 5px 8px;
}

.dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.dot-vs { background: #2f9a67; }
.dot-s { background: #2f66b3; }
.dot-n { background: #c58a2f; }
.dot-d { background: #bf4d5c; }

@media (max-width: 992px) {
    .hero-content {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
