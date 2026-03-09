<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const libraryItems = [
    {
        title: 'Accounts',
        description: 'Manage user accounts and permissions',
        href: '/accounts',
        icon: 'ri-account-circle-line',
        toneClass: 'tone-blue',
    },
    {
        title: 'Assignatorees',
        description: 'Manage assignees and their assignments',
        href: '/assignatorees',
        icon: 'ri-team-line',
        toneClass: 'tone-green',
    },
    {
        title: 'Service Units',
        description: 'Configure and manage service units',
        href: '/service_units',
        icon: 'ri-building-4-line',
        toneClass: 'tone-cyan',
    },
    {
        title: 'Regions',
        description: 'Manage regional configurations',
        href: '/regions',
        icon: 'ri-map-pin-line',
        toneClass: 'tone-orange',
    },
    {
        title: 'PSTOs',
        description: 'Manage PSTO configurations',
        href: '/pstos',
        icon: 'ri-building-line',
        toneClass: 'tone-coral',
    },
    {
        title: 'Unit PSTOs',
        description: 'Configure unit-specific PSTOs',
        href: '/unit-pstos',
        icon: 'ri-map-pin-range-line',
        toneClass: 'tone-amber',
    },
    {
        title: 'Sub Unit PSTOs',
        description: 'Manage sub-unit PSTO mappings',
        href: '/sub-unit-pstos',
        icon: 'ri-map-pin-2-line',
        toneClass: 'tone-teal',
    },
    {
        title: 'CSF Date Display',
        description: 'View and manage CSF form dates',
        href: '/show-date-csf-form',
        icon: 'ri-calendar-line',
        toneClass: 'tone-indigo',
    },
];
</script>

<template>
    <AppLayout title="Libraries">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Libraries</h2>
                <p class="page-heading-subtitle mb-0">Manage core system configurations and reference data.</p>
            </div>
        </template>

        <div class="libraries-page py-4">
            <div class="container-fluid px-3 px-md-4" style="max-width: 1400px;">
                <div class="summary-hero mb-4">
                    <div class="summary-hero-content">
                        <div>
                            <p class="summary-kicker mb-1">System Configuration</p>
                            <h3 class="summary-title mb-1">Libraries Management</h3>
                            <p class="summary-text mb-0">Select a library module to maintain your operational data.</p>
                        </div>
                        <div class="summary-stats">
                            <div class="stat-pill">
                                <span class="stat-label">Modules</span>
                                <span class="stat-value">{{ libraryItems.length }}</span>
                            </div>
                            <div class="stat-pill">
                                <span class="stat-label">Category</span>
                                <span class="stat-value">Administration</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="libraries-shell">
                    <div class="row g-3 g-md-4">
                        <div v-for="item in libraryItems" :key="item.href" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <Link :href="item.href" class="text-decoration-none">
                                <article class="library-card h-100" :class="item.toneClass">
                                    <div class="library-card-body">
                                        <div class="library-icon-wrap">
                                            <i class="ri-2x" :class="item.icon"></i>
                                        </div>
                                        <h5 class="library-title">{{ item.title }}</h5>
                                        <p class="library-description mb-0">{{ item.description }}</p>
                                        <div class="library-arrow">
                                            <i class="ri-arrow-right-up-line"></i>
                                        </div>
                                    </div>
                                </article>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.libraries-page {
    --brand-navy: #153b70;
    --brand-blue: #2266a8;
    --brand-sky: #e9f3ff;
    --text-strong: #12243a;
    --text-soft: #5b7088;
    background: linear-gradient(135deg, #f5f9ff 0%, #edf3fb 100%);
    min-height: 100vh;
}

.page-heading-title {
    margin: 0;
    color: var(--text-strong);
    font-size: 1.25rem;
    font-weight: 700;
}

.page-heading-subtitle {
    margin-top: 2px;
    color: var(--text-soft);
    font-size: 0.9rem;
}

.summary-hero {
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background:
        radial-gradient(circle at top right, rgba(71, 153, 233, 0.3) 0, rgba(71, 153, 233, 0) 45%),
        linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
    overflow: hidden;
}

.summary-hero-content {
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.summary-kicker {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #3f6c9e;
    font-weight: 700;
}

.summary-title {
    color: var(--text-strong);
    font-size: 1.35rem;
    font-weight: 800;
}

.summary-text {
    color: #38506b;
    font-size: 0.92rem;
}

.summary-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.stat-pill {
    background: #ffffff;
    border: 1px solid #d3e4f8;
    border-radius: 12px;
    min-width: 120px;
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(27, 72, 122, 0.08);
}

.stat-label {
    color: #5f7893;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
}

.stat-value {
    color: #0d2f54;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
}

.libraries-shell {
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid #d8e5f5;
    padding: 18px;
    box-shadow: 0 10px 26px rgba(21, 59, 112, 0.08);
}

.library-card {
    border: 1px solid #dae6f5;
    border-radius: 14px;
    background: #ffffff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    position: relative;
    overflow: hidden;
}

.library-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgba(17, 40, 69, 0.12);
    border-color: #b7cde8;
}

.library-card-body {
    padding: 18px 16px 15px;
    height: 100%;
    position: relative;
}

.library-icon-wrap {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border: 1px solid transparent;
}

.library-title {
    color: #1a3658;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 4px;
}

.library-description {
    color: #61758e;
    font-size: 0.84rem;
    line-height: 1.4;
    padding-right: 24px;
}

.library-arrow {
    position: absolute;
    right: 12px;
    bottom: 10px;
    color: #6f87a3;
    transition: transform 0.2s ease;
}

.library-card:hover .library-arrow {
    transform: translate(2px, -2px);
}

.tone-blue .library-icon-wrap {
    background: #eaf1ff;
    color: #2f66b3;
    border-color: #d1e0f7;
}

.tone-green .library-icon-wrap {
    background: #e8f9f1;
    color: #1f9d65;
    border-color: #cdeedc;
}

.tone-cyan .library-icon-wrap {
    background: #eaf8ff;
    color: #1484ba;
    border-color: #d1edf8;
}

.tone-orange .library-icon-wrap {
    background: #fff3e8;
    color: #d97329;
    border-color: #f7dfc7;
}

.tone-coral .library-icon-wrap {
    background: #fff0ec;
    color: #d35a49;
    border-color: #f5d5cf;
}

.tone-amber .library-icon-wrap {
    background: #fff7e8;
    color: #cc8b1f;
    border-color: #f5e2bc;
}

.tone-teal .library-icon-wrap {
    background: #e9f8f6;
    color: #198c83;
    border-color: #ccece7;
}

.tone-indigo .library-icon-wrap {
    background: #eef1ff;
    color: #4f63c7;
    border-color: #d7def8;
}

@media (max-width: 992px) {
    .summary-hero-content {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
