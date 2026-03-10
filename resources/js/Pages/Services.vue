<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { reactive, watch, ref, onMounted } from "vue";
import AOS from 'aos'
import 'aos/dist/aos.css'
import { router } from '@inertiajs/vue3'

AOS.init();

defineProps({
    region_id: Number,
    region: Object,
    services: Object,
});

const goServiceUnits = async (region_id,service_id) => {
    router.get(`/services/csf/service_units?region_id=`+region_id + `&service_id=`+service_id)
}

const goBack = async () => {
    window.history.back()
}

</script>

<template>
    <Head title="Services" />
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" data-aos="fade-down" data-aos-duration="500" data-aos-delay="500" style="backdrop-filter: blur(2px);">
        <div class="container-fluid">
            <a href="/" class="navbar-brand d-flex align-items-center">
                <img src="../../../public/images/dost-logo.jpg" class="me-3" alt="DOST Logo" style="height: 2rem;">
                <span class="fw-bold fs-4">DOST <span v-if="region">{{ region.code }}</span> Customer Relation Management System</span>
            </a>
        </div>
    </nav>
    <div class="container-fluid d-flex flex-column min-vh-100 services-page">
        <div class="mx-5" style="margin-top: 100px;">
            <div class="services-hero" data-aos="fade-up">
                <div class="services-hero-content">
                    <div>
                        <p class="services-kicker mb-1">Available</p>
                        <h2 class="services-title mb-1">Our Services</h2>
                        <p class="services-text mb-0">Select a service to view service units</p>
                    </div>
                    <div class="services-stats">
                        <div class="stat-pill">
                            <span class="stat-label">Services</span>
                            <span class="stat-value">{{ services?.length || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mx-4 mt-5">
            <div class="row justify-content-center">
                <div v-for="(service, index) in services" :key="service.id" class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center mb-4" :data-aos="'zoom-in'" :data-aos-delay="index * 10">
                    <Link :href="'/services/csf/service_units?region_id=' + region_id + '&service_id=' + service.id" class="card-link">
                        <div class="service-card">
                            <div class="service-card-body">
                                <div class="service-icon-wrapper">
                                    <i class="ri-service-line service-icon"></i>
                                </div>
                                <p class="service-name">{{ service.services_name }}</p>
                            </div>
                            <div class="service-card-footer">
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
                <i class="ri-arrow-left-line me-2"></i> Back to Regions
            </button>
        </div>
    </div>
</template>

<style scoped>
.services-page {
    background-color: #f6f9fc;
    min-height: 100vh;
}

.services-hero {
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background-color: #f6fbff;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(21, 59, 112, 0.08);
}

.services-hero-content {
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.services-kicker {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #3f6c9e;
    font-weight: 700;
}

.services-title {
    color: #153b70;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
}

.services-text {
    color: #38506b;
    font-size: 1rem;
}

.services-stats {
    display: flex;
    gap: 12px;
}

.stat-pill {
    background-color: #ffffff;
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

.service-card {
    width: 240px;
    border-radius: 16px;
    overflow: hidden;
    background-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(21, 59, 112, 0.15);
}

.service-card:hover .service-card-body {
    background-color: #f0f7ff;
}

.service-card:hover .service-card-footer {
    background-color: #153b70;
}

.service-card:hover .service-card-footer .explore-text,
.service-card:hover .service-card-footer i {
    color: #ffffff;
}

.service-card:hover .service-icon {
    color: #153b70;
    transform: scale(1.1);
}

.service-card-body {
    padding: 32px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background-color: #ffffff;
    transition: all 0.3s ease;
    min-height: 140px;
}

.service-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #e8f2ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    transition: all 0.3s ease;
}

.service-icon {
    font-size: 1.75rem;
    color: #2266a8;
    transition: all 0.3s ease;
}

.service-name {
    font-size: 1rem;
    font-weight: 700;
    color: #153b70;
    margin: 0;
    line-height: 1.3;
}

.service-card-footer {
    padding: 12px 16px;
    background-color: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    border-top: 1px solid #e2e8f0;
}

.explore-text {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.service-card-footer i {
    color: #94a3b8;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-back {
    background-color: #ffffff;
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
    background-color: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    color: #0f172a;
}

@media (max-width: 992px) {
    .services-hero-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .services-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

