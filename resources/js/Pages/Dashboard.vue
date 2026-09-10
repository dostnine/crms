<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, onMounted } from 'vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    module_counts: { type: Object, default: () => ({}) },
    distribution: { type: Object, default: () => ({}) },
    filters: {
        type: Object,
        default: () => ({ rating_filter: 'all', date_filter: 'all' }),
    },
    available_years: { type: Array, default: () => [] },
});

const ratingFilters = [
    { value: 'all', label: 'All Ratings', icon: 'ri-bar-chart-line' },
    { value: 'positive', label: 'Positive (4-5)', icon: 'ri-emotion-happy-line' },
    { value: 'neutral', label: 'Neutral (3)', icon: 'ri-emotion-normal-line' },
    { value: 'negative', label: 'Negative (1-2)', icon: 'ri-emotion-unhappy-line' },
];

// Mirrors the report builder: the period type is the top-level choice, and
// picking one reveals its selector defaulted to the current period.
const dateFilters = [
    { value: 'all', label: 'All Time' },
    { value: 'today', label: 'Today' },
    { value: 'week', label: 'This Week' },
    { value: 'month', label: 'By Month' },
    { value: 'quarter', label: 'By Quarter' },
    { value: 'year', label: 'By Year' },
    { value: 'range', label: 'Date Range', icon: 'ri-calendar-2-line' },
];

const monthOptions = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
].map((label, i) => ({ value: i + 1, label }));

const quarterOptions = [
    { value: 1, label: 'Q1 (Jan-Mar)' },
    { value: 2, label: 'Q2 (Apr-Jun)' },
    { value: 3, label: 'Q3 (Jul-Sep)' },
    { value: 4, label: 'Q4 (Oct-Dec)' },
];

const currentRatingFilter = computed(() => props.filters.rating_filter || 'all');
const currentDateFilter = computed(() => props.filters.date_filter || 'all');

// Which extra selector (if any) the chosen period needs.
const needsMonth = computed(() => currentDateFilter.value === 'month');
const needsQuarter = computed(() => currentDateFilter.value === 'quarter');
const needsYear = computed(() => ['month', 'quarter', 'year'].includes(currentDateFilter.value));
const needsRange = computed(() => currentDateFilter.value === 'range');
const showPeriodPicker = computed(() => needsYear.value || needsRange.value);

const now = new Date();

// Seeded from whatever the server resolved, so the pickers keep their values
// across reloads and default to the current period on first use.
const period = reactive({
    year: props.filters.year || now.getFullYear(),
    month: props.filters.month || now.getMonth() + 1,
    quarter: props.filters.quarter || Math.floor(now.getMonth() / 3) + 1,
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const today = now.toLocaleDateString(undefined, {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
});

const buildFilterUrl = (ratingFilter, dateFilter) => {
    const params = new URLSearchParams();
    if (ratingFilter !== 'all') params.set('rating_filter', ratingFilter);
    if (dateFilter !== 'all') params.set('date_filter', dateFilter);

    if (dateFilter === 'range') {
        if (period.date_from) params.set('date_from', period.date_from);
        if (period.date_to) params.set('date_to', period.date_to);
    } else if (['month', 'quarter', 'year'].includes(dateFilter)) {
        params.set('year', period.year);
        if (dateFilter === 'month') params.set('month', period.month);
        if (dateFilter === 'quarter') params.set('quarter', period.quarter);
    }

    const query = params.toString();
    return query ? `?${query}` : '/dashboard';
};

const reload = () => {
    router.get(buildFilterUrl(currentRatingFilter.value, currentDateFilter.value), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Dropdowns reload straight away; a date range waits for Apply since it needs
// both ends before it means anything.
const onPeriodChange = () => {
    if (!needsRange.value) reload();
};

const canApplyRange = computed(() => Boolean(period.date_from || period.date_to));

const distributionStops = computed(() => {
    const vs = Number(props.distribution?.very_satisfied?.pct || 0);
    const s = Number(props.distribution?.satisfied?.pct || 0);
    const n = Number(props.distribution?.neutral?.pct || 0);
    const d = Number(props.distribution?.dissatisfied?.pct || 0);
    return {
        stop1: vs.toFixed(2),
        stop2: (vs + s).toFixed(2),
        stop3: (vs + s + n).toFixed(2),
        stop4: (vs + s + n + d).toFixed(2),
    };
});

// Every card reflects the selected period. "Active Users" and "Pending
// Reviews" were dropped: the first is a system-wide count that ignored the
// filter entirely, and the second was always 0 for this data.
const quickStats = computed(() => [
    { label: 'Total Surveys', key: 'total_surveys', icon: 'ri-survey-line', tone: 'blue', suffix: '', value: props.stats?.total_surveys ?? 0 },
    { label: 'Respondents', key: 'total_respondents', icon: 'ri-group-line', tone: 'teal', suffix: '', value: props.stats?.total_respondents ?? 0 },
    { label: 'Total Ratings', key: 'total_ratings', icon: 'ri-list-check-2', tone: 'indigo', suffix: '', value: props.stats?.total_ratings ?? 0 },
    { label: 'Satisfaction Rate', key: 'satisfaction_rate', icon: 'ri-star-smile-line', tone: 'green', suffix: '%', value: props.stats?.satisfaction_rate ?? 0 },
    { label: 'Average Rating', key: 'average_rating', icon: 'ri-speed-up-line', tone: 'amber', suffix: ' / 5', decimals: 2, value: props.stats?.average_rating ?? 0 },
]);

const filteredRatings = computed(() => {
    const total = props.stats?.filtered_total_ratings ?? 0;
    const pct = (v) => (total > 0 ? Math.round((v / total) * 100) : 0);
    return [
        { label: 'Very Satisfied', key: 'filtered_very_satisfied', icon: 'ri-emotion-happy-line', color: '#16a34a', value: props.stats?.filtered_very_satisfied ?? 0, pct: pct(props.stats?.filtered_very_satisfied ?? 0) },
        { label: 'Satisfied', key: 'filtered_satisfied', icon: 'ri-emotion-normal-line', color: '#2f66b3', value: props.stats?.filtered_satisfied ?? 0, pct: pct(props.stats?.filtered_satisfied ?? 0) },
        { label: 'Neutral', key: 'filtered_neutral', icon: 'ri-emotion-normal-line', color: '#d99a2b', value: props.stats?.filtered_neutral ?? 0, pct: pct(props.stats?.filtered_neutral ?? 0) },
        { label: 'Dissatisfied', key: 'filtered_dissatisfied', icon: 'ri-emotion-unhappy-line', color: '#e05260', value: props.stats?.filtered_dissatisfied ?? 0, pct: pct(props.stats?.filtered_dissatisfied ?? 0) },
    ];
});

const modules = [
    { title: 'User Accounts', description: 'Manage user accounts and permissions', href: '/accounts', icon: 'ri-user-settings-line', badgeKey: 'users', badgeSuffix: 'Users', tone: 'blue' },
    { title: 'Service Units', description: 'Configure service units and departments', href: '/service_units', icon: 'ri-building-line', badgeKey: 'units', badgeSuffix: 'Units', tone: 'green' },
    { title: 'Regions', description: 'Manage regional offices and locations', href: '/regions', icon: 'ri-map-pin-line', badgeKey: 'regions', badgeSuffix: 'Regions', tone: 'cyan' },
    { title: 'PSTOs', description: 'Handle PSTO configurations and operations', href: '/pstos', icon: 'ri-store-2-line', badgeKey: 'pstos', badgeSuffix: 'PSTOs', tone: 'coral' },
];

// Count-up animation for headline numbers.
const displays = reactive({});
quickStats.value.forEach((s) => (displays[s.key] = 0));
displays.mix_total = 0;

const prefersReducedMotion =
    typeof window !== 'undefined' && window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function countUp(key, to, duration = 1100) {
    to = Number(to) || 0;
    if (prefersReducedMotion || to === 0) { displays[key] = to; return; }
    const start = performance.now();
    const tick = (now) => {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        displays[key] = to * eased;
        if (p < 1) requestAnimationFrame(tick);
        else displays[key] = to;
    };
    requestAnimationFrame(tick);
}

const fmt = (stat) => {
    const v = displays[stat.key] ?? 0;
    if (stat.suffix === '%') return v.toFixed(1);
    // Average Rating is a decimal score -- rounding it to a whole number
    // would show 4.75 as "5".
    if (stat.decimals) return v.toFixed(stat.decimals);
    return Math.round(v).toLocaleString();
};

onMounted(() => {
    quickStats.value.forEach((s) => countUp(s.key, s.value));
    countUp('mix_total', props.distribution?.total_ratings ?? 0);
});
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
                <!-- Hero -->
                <section class="hero-card mb-4">
                    <div class="hero-glow"></div>
                    <div class="hero-content">
                        <div class="hero-copy">
                            <p class="hero-kicker mb-2"><i class="ri-sparkling-2-line me-1"></i>DOST · CRMS CSF</p>
                            <h1 class="hero-title mb-1">Welcome back<template v-if="$page.props.auth.user.name">, {{ $page.props.auth.user.name }}</template></h1>
                            <p class="hero-text mb-0">{{ today }} — track activity, monitor satisfaction, and open modules quickly.</p>
                        </div>
                        <div class="hero-icon"><i class="ri-dashboard-3-line"></i></div>
                    </div>
                </section>

                <!-- Quick stats -->
                <section class="mb-4">
                    <div class="row g-3">
                        <div v-for="item in quickStats" :key="item.key" class="col-12 col-sm-6 col-lg-4 col-xl">
                            <article class="stat-card" :class="'tone-' + item.tone">
                                <span class="stat-accent"></span>
                                <div class="stat-icon-chip"><i :class="item.icon"></i></div>
                                <div class="stat-main">
                                    <p class="stat-label mb-1">{{ item.label }}</p>
                                    <h3 class="stat-value mb-0">{{ fmt(item) }}{{ item.suffix }}</h3>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- Filters -->
                <section class="filter-section mb-4">
                    <div class="filter-card">
                        <div class="filter-header">
                            <h3 class="filter-title"><i class="ri-filter-3-line me-2"></i>Filter Data</h3>
                            <span v-if="currentRatingFilter !== 'all' || currentDateFilter !== 'all'" class="filter-badge"><i class="ri-check-line me-1"></i>Active</span>
                        </div>
                        <div class="filter-body">
                            <div class="filter-group">
                                <label class="filter-label">Time Period</label>
                                <div class="filter-buttons">
                                    <Link v-for="dateOption in dateFilters" :key="dateOption.value" :href="buildFilterUrl(currentRatingFilter, dateOption.value)" class="filter-btn" :class="{ active: currentDateFilter === dateOption.value }"><i v-if="dateOption.icon" :class="dateOption.icon" class="me-1"></i>{{ dateOption.label }}</Link>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Rating Type</label>
                                <div class="filter-buttons">
                                    <Link v-for="ratingOption in ratingFilters" :key="ratingOption.value" :href="buildFilterUrl(ratingOption.value, currentDateFilter)" class="filter-btn" :class="{ active: currentRatingFilter === ratingOption.value, 'btn-positive': ratingOption.value === 'positive', 'btn-neutral': ratingOption.value === 'neutral', 'btn-negative': ratingOption.value === 'negative' }"><i :class="ratingOption.icon" class="me-1"></i>{{ ratingOption.label }}</Link>
                                </div>
                            </div>
                        </div>

                        <!-- Period selector for the chosen type, like the report builder -->
                        <div v-if="showPeriodPicker" class="custom-period">
                            <div class="custom-row">
                                <div v-if="needsMonth" class="custom-field">
                                    <label class="filter-label">Month</label>
                                    <select v-model.number="period.month" class="form-select form-select-sm" @change="onPeriodChange">
                                        <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                </div>

                                <div v-if="needsQuarter" class="custom-field">
                                    <label class="filter-label">Quarter</label>
                                    <select v-model.number="period.quarter" class="form-select form-select-sm" @change="onPeriodChange">
                                        <option v-for="q in quarterOptions" :key="q.value" :value="q.value">{{ q.label }}</option>
                                    </select>
                                </div>

                                <div v-if="needsYear" class="custom-field">
                                    <label class="filter-label">Year</label>
                                    <select v-model.number="period.year" class="form-select form-select-sm" @change="onPeriodChange">
                                        <option v-for="y in props.available_years" :key="y" :value="y">{{ y }}</option>
                                    </select>
                                </div>

                                <template v-if="needsRange">
                                    <div class="custom-field">
                                        <label class="filter-label">From</label>
                                        <input v-model="period.date_from" type="date" class="form-control form-control-sm">
                                    </div>
                                    <div class="custom-field">
                                        <label class="filter-label">To</label>
                                        <input v-model="period.date_to" type="date" class="form-control form-control-sm">
                                    </div>
                                    <div class="custom-field custom-actions">
                                        <button type="button" class="btn btn-primary btn-sm" :disabled="!canApplyRange" @click="reload">
                                            <i class="ri-search-line me-1"></i>Apply
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <p v-if="needsRange && !canApplyRange" class="custom-hint mb-0">Pick a From and/or To date to apply this range.</p>
                        </div>

                        <div v-if="currentRatingFilter !== 'all' || currentDateFilter !== 'all'" class="filter-summary">
                            <span class="summary-text">Showing <strong>{{ (props.stats?.filtered_total_respondents ?? 0).toLocaleString() }}</strong> respondents<span v-if="currentRatingFilter !== 'all'"> who gave a <strong>{{ currentRatingFilter }}</strong> score</span><span v-if="props.filters?.period_label && props.filters.period_label !== 'All Time'"> within <strong>{{ props.filters.period_label }}</strong></span><span v-else> across <strong>all time</strong></span></span>
                            <Link :href="buildFilterUrl('all', 'all')" class="clear-filter"><i class="ri-close-line me-1"></i> Clear Filters</Link>
                        </div>
                    </div>
                </section>

                <!-- Modules + Satisfaction mix -->
                <section class="row g-3 g-lg-4">
                    <div class="col-12 col-lg-8">
                        <div class="panel-card h-100">
                            <div class="panel-head"><h3 class="panel-title mb-0"><i class="ri-apps-2-line me-2"></i>Management Modules</h3></div>
                            <div class="panel-body">
                                <div class="row g-3">
                                    <div v-for="module in modules" :key="module.href" class="col-12 col-sm-6">
                                        <Link :href="module.href" class="module-link">
                                            <article class="module-tile" :class="'tone-' + module.tone">
                                                <div class="module-icon"><i :class="module.icon"></i></div>
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
                            <div class="panel-head"><h3 class="panel-title mb-0"><i class="ri-pie-chart-2-line me-2"></i>Satisfaction Mix</h3></div>
                            <div class="panel-body">
                                <div class="mix-wrap mb-3">
                                    <div class="mix-pie" :style="{ background: `conic-gradient(#16a34a 0% ${distributionStops.stop1}%, #2f66b3 ${distributionStops.stop1}% ${distributionStops.stop2}%, #d99a2b ${distributionStops.stop2}% ${distributionStops.stop3}%, #e05260 ${distributionStops.stop3}% ${distributionStops.stop4}%, #e6edf6 ${distributionStops.stop4}% 100%)` }"></div>
                                    <div class="mix-center">
                                        <div class="mix-total">{{ Math.round(displays.mix_total).toLocaleString() }}</div>
                                        <div class="mix-caption">Total Ratings</div>
                                    </div>
                                </div>
                                <div class="mix-list">
                                    <div class="mix-item"><span class="mix-left"><span class="dot dot-vs"></span>Very Satisfied</span> <strong>{{ props.distribution?.very_satisfied?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="mix-left"><span class="dot dot-s"></span>Satisfied</span> <strong>{{ props.distribution?.satisfied?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="mix-left"><span class="dot dot-n"></span>Neutral</span> <strong>{{ props.distribution?.neutral?.pct ?? 0 }}%</strong></div>
                                    <div class="mix-item"><span class="mix-left"><span class="dot dot-d"></span>Dissatisfied</span> <strong>{{ props.distribution?.dissatisfied?.pct ?? 0 }}%</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Filtered breakdown -->
                <section v-if="currentRatingFilter !== 'all'" class="mt-4">
                    <div class="panel-card">
                        <div class="panel-head"><h3 class="panel-title mb-0"><i class="ri-bar-chart-grouped-line me-2"></i>Filtered Rating Breakdown</h3></div>
                        <div class="panel-body">
                            <div class="row g-3">
                                <div v-for="rating in filteredRatings" :key="rating.key" class="col-6 col-md-3">
                                    <div class="rating-card" :style="{ borderLeftColor: rating.color }">
                                        <div class="rating-icon" :style="{ backgroundColor: rating.color + '20', color: rating.color }"><i :class="rating.icon"></i></div>
                                        <div class="rating-info">
                                            <p class="rating-label mb-0">{{ rating.label }}</p>
                                            <h4 class="rating-value mb-0">{{ rating.value }} <span class="rating-pct">({{ rating.pct }}%)</span></h4>
                                        </div>
                                    </div>
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
.dashboard-page { background: linear-gradient(180deg, #f4f8ff 0%, #eef3fb 100%); min-height: 100vh; }
.page-heading-title { margin: 0; color: #0d2f54; font-size: 1.25rem; font-weight: 800; }
.page-heading-subtitle { color: #5b7088; font-size: 0.9rem; }

/* Hero */
.hero-card {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    background: linear-gradient(120deg, #10214a 0%, #123a6b 55%, #0d2f54 100%);
    box-shadow: 0 18px 40px rgba(13, 47, 84, 0.28);
}
.hero-glow {
    position: absolute;
    top: -60%;
    right: -10%;
    width: 460px;
    height: 460px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.35) 0%, rgba(56, 189, 248, 0) 65%);
    pointer-events: none;
}
.hero-content { position: relative; padding: 26px 28px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
.hero-kicker { font-size: 0.76rem; letter-spacing: 1.5px; text-transform: uppercase; color: #7dd3fc; font-weight: 700; }
.hero-title { font-size: 1.6rem; color: #fff; font-weight: 800; letter-spacing: -0.3px; }
.hero-text { color: #b7c6e6; font-size: 0.92rem; }
.hero-icon {
    flex-shrink: 0;
    width: 68px;
    height: 68px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(125, 211, 252, 0.14);
    border: 1px solid rgba(125, 211, 252, 0.25);
}
.hero-icon i { font-size: 2rem; color: #7dd3fc; }

/* Stat cards */
.stat-card {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid #e2ecfa;
    background: #fff;
    border-radius: 16px;
    padding: 18px 18px;
    box-shadow: 0 6px 18px rgba(13, 47, 84, 0.07);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(13, 47, 84, 0.14); }
.stat-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; }
.stat-icon-chip {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
}
.stat-label { font-size: 0.78rem; color: #64789a; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
.stat-value { font-size: 1.7rem; color: #0d2f54; font-weight: 800; line-height: 1; }

.tone-blue .stat-accent { background: linear-gradient(90deg, #38bdf8, #2f66b3); }
.tone-blue .stat-icon-chip { background: linear-gradient(135deg, #38bdf8, #2f66b3); }
.tone-teal .stat-accent { background: linear-gradient(90deg, #22d3ee, #0e7490); }
.tone-teal .stat-icon-chip { background: linear-gradient(135deg, #22d3ee, #0e7490); }
.tone-green .stat-accent { background: linear-gradient(90deg, #34d399, #059669); }
.tone-green .stat-icon-chip { background: linear-gradient(135deg, #34d399, #059669); }
.tone-indigo .stat-accent { background: linear-gradient(90deg, #818cf8, #4338ca); }
.tone-indigo .stat-icon-chip { background: linear-gradient(135deg, #818cf8, #4338ca); }
.tone-amber .stat-accent { background: linear-gradient(90deg, #fbbf24, #d97706); }
.tone-amber .stat-icon-chip { background: linear-gradient(135deg, #fbbf24, #d97706); }

/* Filter card */
.filter-card { background: #fff; border: 1px solid #e2ecfa; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(13, 47, 84, 0.07); }
.filter-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: linear-gradient(90deg, #10214a, #123a6b); }
.filter-title { color: #fff; font-size: 0.95rem; font-weight: 700; margin: 0; }
.filter-badge { background: linear-gradient(135deg, #34d399, #059669); color: #fff; font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
.filter-body { padding: 18px; display: flex; flex-wrap: wrap; gap: 22px; }
.filter-group { flex: 1; min-width: 250px; }
.filter-label { display: block; font-size: 0.78rem; font-weight: 700; color: #3f6c9e; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-buttons { display: flex; flex-wrap: wrap; gap: 8px; }
.filter-btn { display: inline-flex; align-items: center; padding: 8px 15px; font-size: 0.85rem; font-weight: 600; color: #3f6c9e; background: #f1f6fc; border: 1px solid #d8e5f5; border-radius: 999px; text-decoration: none; transition: all 0.2s ease; }
.filter-btn:hover { background: #e4eef9; border-color: #b8cce2; color: #10214a; transform: translateY(-1px); }
.filter-btn.active { background: linear-gradient(135deg, #38bdf8, #2f66b3); border-color: transparent; color: #fff; box-shadow: 0 6px 16px rgba(56, 189, 248, 0.35); }
.filter-btn.btn-positive.active { background: linear-gradient(135deg, #34d399, #059669); box-shadow: 0 6px 16px rgba(52, 211, 153, 0.35); }
.filter-btn.btn-neutral.active { background: linear-gradient(135deg, #fbbf24, #d97706); box-shadow: 0 6px 16px rgba(251, 191, 36, 0.35); }
.filter-btn.btn-negative.active { background: linear-gradient(135deg, #f87171, #dc2626); box-shadow: 0 6px 16px rgba(248, 113, 113, 0.35); }
.custom-period { padding: 14px 18px; background: #f7fafd; border-top: 1px solid #dbe7f4; }
.custom-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.custom-field { display: flex; flex-direction: column; gap: 4px; min-width: 150px; }
.custom-field .filter-label { margin-bottom: 0; }
.custom-actions { min-width: auto; }
.custom-hint { margin-top: 8px; font-size: 0.8rem; color: #6b7c93; }

.filter-summary { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; background: #f0f9f4; border-top: 1px solid #dbe7f4; flex-wrap: wrap; gap: 8px; }
.summary-text { font-size: 0.85rem; color: #3f6c9e; }
.summary-text strong { color: #10214a; }
.clear-filter { font-size: 0.85rem; font-weight: 600; color: #dc2626; text-decoration: none; display: inline-flex; align-items: center; }
.clear-filter:hover { color: #b91c1c; text-decoration: underline; }

/* Panels */
.panel-card { border: 1px solid #e2ecfa; border-radius: 18px; overflow: hidden; background: #fff; box-shadow: 0 10px 28px rgba(13, 47, 84, 0.08); }
.panel-head { padding: 14px 18px; background: linear-gradient(90deg, #10214a, #123a6b); }
.panel-title { color: #fff; font-size: 0.95rem; font-weight: 700; }
.panel-body { padding: 16px; }

/* Module tiles */
/* The global `a:not(nav a)` rule in app.css leaves these links without any
   focus styling, so a clicked tile keeps the browser's default dark ring and
   reads as a black border. Suppress that and use an on-brand ring instead --
   only for keyboard focus, so clicking doesn't leave a tile outlined. */
.module-link { display: block; text-decoration: none; color: inherit; border-radius: 14px; outline: none; }
.module-link:focus { outline: none; box-shadow: none; }
.module-link:focus-visible { outline: 2px solid #2f66b3; outline-offset: 3px; }

.module-tile { display: grid; grid-template-columns: 48px 1fr 20px; gap: 12px; align-items: center; border: 1px solid #e2ecfa; border-radius: 14px; padding: 14px; background: #fff; transition: all 0.25s ease; }
.module-tile:hover { border-color: #bcd6f2; box-shadow: 0 10px 22px rgba(13, 47, 84, 0.12); transform: translateY(-3px); }
.module-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; }
.module-title { font-size: 0.98rem; color: #10214a; font-weight: 800; }
.module-desc { font-size: 0.8rem; color: #607891; }
.module-badge { display: inline-block; font-size: 0.72rem; font-weight: 700; color: #2f66b3; background: #eaf2ff; border: 1px solid #cfe0f5; border-radius: 999px; padding: 3px 10px; }
.module-arrow { color: #9db3cd; font-size: 1.1rem; transition: transform 0.25s ease, color 0.25s ease; }
.module-tile:hover .module-arrow { transform: translate(3px, -3px); color: #2f66b3; }
.tone-blue .module-icon { background: linear-gradient(135deg, #38bdf8, #2f66b3); }
.tone-green .module-icon { background: linear-gradient(135deg, #34d399, #059669); }
.tone-cyan .module-icon { background: linear-gradient(135deg, #22d3ee, #0e7490); }
.tone-coral .module-icon { background: linear-gradient(135deg, #fb923c, #ea580c); }

/* Satisfaction mix */
.mix-wrap { position: relative; width: 180px; height: 180px; margin: 6px auto 4px; animation: pop 0.6s cubic-bezier(0.22, 1, 0.36, 1) both; }
.mix-pie { width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 8px 22px rgba(13, 47, 84, 0.14); }
.mix-center { position: absolute; inset: 26% 26%; border-radius: 50%; background: #fff; box-shadow: inset 0 0 0 1px #e6eef8; display: flex; align-items: center; justify-content: center; flex-direction: column; }
.mix-total { font-size: 1.35rem; font-weight: 800; color: #10214a; line-height: 1; }
.mix-caption { font-size: 0.68rem; color: #667f99; margin-top: 2px; }
.mix-list { display: grid; gap: 7px; }
.mix-item { display: flex; align-items: center; justify-content: space-between; font-size: 0.84rem; color: #365272; border: 1px solid #e4edf8; border-radius: 10px; padding: 8px 12px; transition: background 0.2s ease; }
.mix-item:hover { background: #f5f9ff; }
.mix-item strong { color: #10214a; }
.mix-left { display: inline-flex; align-items: center; }
.dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
.dot-vs { background: #16a34a; }
.dot-s { background: #2f66b3; }
.dot-n { background: #d99a2b; }
.dot-d { background: #e05260; }

/* Filtered breakdown */
.rating-card { display: flex; align-items: center; gap: 12px; padding: 14px; background: #fff; border: 1px solid #e6edf6; border-left-width: 4px; border-radius: 12px; transition: all 0.2s ease; }
.rating-card:hover { box-shadow: 0 8px 18px rgba(13, 47, 84, 0.1); transform: translateY(-2px); }
.rating-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.rating-label { font-size: 0.75rem; color: #657e99; font-weight: 600; }
.rating-value { font-size: 1.15rem; color: #10214a; font-weight: 800; }
.rating-pct { font-size: 0.8rem; font-weight: 600; color: #657e99; }

@keyframes pop { from { opacity: 0; transform: scale(0.86); } to { opacity: 1; transform: scale(1); } }

@media (max-width: 992px) {
    .hero-content { flex-direction: column; align-items: flex-start; }
    .filter-group { min-width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
    .stat-card:hover, .module-tile:hover, .rating-card:hover { transform: none; }
    .mix-wrap { animation: none; }
}
</style>
