<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { reactive, watch, ref, onMounted } from "vue";
import AOS from 'aos'
import 'aos/dist/aos.css'
import { router } from '@inertiajs/vue3'

AOS.init();

defineProps({
    regions: Object,
});

const goServices = async (region_id) => {
    router.get('/services/csf/services',{region_id} )
}

const goBack = async () => {
    window.history.back()
}

</script>

<template>
    <Head title="Regions" />
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" data-aos="fade-down" data-aos-duration="500" data-aos-delay="500" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; backdrop-filter: blur(2px);">
        <div class="container-fluid">
            <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
                <img src="../../../public/images/dost-logo.jpg" alt="DOST Logo" class="me-2" style="height: 2rem;">
                <span class="fw-bold fs-4">DOST Customer Relation Management System</span>
            </a>
        </div>
    </nav>
    <div class="min-vh-100 d-flex flex-column region-page">
        <div class="mt-5 mx-3">
            <div class="region-hero" data-aos="fade-up">
                <div class="region-hero-content">
                    <div>
                        <p class="region-kicker mb-1">Welcome to</p>
                        <h2 class="region-title mb-1">DOST Regional Offices</h2>
                        <p class="region-text mb-0">Select your region to continue to available services</p>
                    </div>
                    <div class="region-stats">
                        <div class="stat-pill">
                            <span class="stat-label">Regions</span>
                            <span class="stat-value">{{ regions?.length || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div v-for="(region, index) in regions" :key="region.id" class="col-lg-4 col-md-4 col-sm-6 m-4" :data-aos="'zoom-in'" :data-aos-delay="index * 10">
                    <Link :href="'/services/csf/services?region_id=' + region.id" class="text-decoration-none">
                        <div class="card h-100 region-card shadow border-0">
                            <div class="region-card-body">
                                <div class="region-icon-wrapper">
                                    <i class="ri-map-pin-line region-icon"></i>
                                </div>
                                <h6 class="card-title fw-bold region-card-title mb-0">{{ region.short_name }}</h6>
                                <p class="region-full-name text-muted mb-0">{{ region.region_name }}</p>
                            </div>
                            <div class="region-card-footer">
                                <span class="explore-text">Click to explore</span>
                                <i class="ri-arrow-right-line"></i>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        <div class="mt-auto text-center mb-4">
            <button @click="goBack()" class="btn btn-back">
                <i class="ri-arrow-left-line me-2"></i> Back
            </button>
        </div>
    </div>
</template>

<style scoped>
.region-page {
    background: linear-gradient(135deg, #f6f9fc 0%, #e8f0f8 100%);
    min-height: 100vh;
}

.region-hero {
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background: linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(21, 59, 112, 0.08);
}

.region-hero-content {
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.region-kicker {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #3f6c9e;
    font-weight: 700;
}

.region-title {
    color: #153b70;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
}

.region-text {
    color: #38506b;
    font-size: 1rem;
}

.region-stats {
    display: flex;
    gap: 12px;
}

.stat-pill {
    background: #ffffff;
    border: 1px solid #d3e4f8;
    border-radius: 12px;
    padding: 10px 18px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(27, 72, 122, 0.08);
}

.stat-label {
    color: #5f7893;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.stat-value {
    color: #0d2f54;
    font-size: 1.25rem;
    font-weight: 800;
}

.region-card {
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
}

.region-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(21, 59, 112, 0.15) !important;
}

.region-card:hover .region-card-body {
    background: linear-gradient(135deg, rgba(21, 59, 112, 0.05) 0%, rgba(34, 102, 168, 0.08) 100%);
}

.region-card:hover .region-card-footer {
    background: linear-gradient(90deg, #153b70, #2266a8);
}

.region-card:hover .region-card-footer .explore-text,
.region-card:hover .region-card-footer i {
    color: #ffffff;
}

.region-card:hover .region-icon {
    color: #153b70;
    transform: scale(1.1);
}

.region-card-body {
    padding: 32px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: #ffffff;
    transition: all 0.3s ease;
    min-height: 160px;
}

.region-icon-wrapper {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8f2ff 0%, #d0e4f8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}

.region-icon {
    font-size: 2rem;
    color: #2266a8;
    transition: all 0.3s ease;
}

.region-card-title {
    color: #153b70;
    font-size: 1.35rem;
    font-weight: 800;
    margin-bottom: 4px;
}

.region-full-name {
    font-size: 0.85rem;
}

.region-card-footer {
    padding: 14px 20px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    border-top: 1px solid #e2e8f0;
}

.explore-text {
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.region-card-footer i {
    color: #94a3b8;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-back {
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    padding: 12px 32px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    color: #334155;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    color: #0f172a;
}

@media (max-width: 992px) {
    .region-hero-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .region-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

