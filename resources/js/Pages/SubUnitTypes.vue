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
    service_id: Number,
    unit_id: Number,
    sub_unit_id:Number,
    sub_unit:Object,
    types:Object,
});

const goNext = async (region_id, service_id, unit_id, sub_unit_id, type_name) => {
    router.get(`/services/csf?region_id=`+ region_id + 
                                `&service_id=`+ service_id + 
                                `&unit_id=`+ unit_id +
                                `&sub_unit_id=` + sub_unit_id +
                                `&sub_unit_type=` + type_name);   
};

const goBack = async () => {
    window.history.back();
};

</script>

<template>
    <Head title="Service Units" />
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" data-aos="fade-down" data-aos-duration="500" data-aos-delay="500" style="backdrop-filter: blur(2px);">
        <div class="container-fluid">
            <a href="/" class="navbar-brand d-flex align-items-center text-decoration-none">
                <img src="../../../public/images/dost-logo.jpg" alt="DOST Logo" style="height: 2rem;" class="me-2">
                <span class="fw-bold fs-4">DOST <span v-if="region">{{ region.code }}</span> Customer Relation Management System</span>
            </a>
        </div>
    </nav>
    <div class="min-vh-100 d-flex flex-column types-page">
        <div class="mx-3" style="margin-top: 100px;">
            <div class="types-hero" data-aos="fade-up">
                <div class="types-hero-content">
                    <div>
                        <p class="types-kicker mb-1">Select</p>
                        <h2 class="types-title mb-1">{{ sub_unit?.sub_unit_name || 'Unit Types' }}</h2>
                        <p class="types-text mb-0">Choose a type to continue</p>
                    </div>
                    <div class="types-stats">
                        <div class="stat-pill">
                            <span class="stat-label">Types</span>
                            <span class="stat-value">{{ types?.length || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div v-for="(type, index) in types" :key="type.id" class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4" :data-aos="'zoom-in'" :data-aos-delay="index * 10">
                    <Link :href="'/services/csf?region_id=' + region_id + '&service_id=' + service_id + '&unit_id=' + unit_id + '&sub_unit_id=' + sub_unit_id + '&sub_unit_type=' + type.type_name" class="text-decoration-none">
                        <div class="type-card">
                            <div class="type-card-body">
                                <div class="type-icon-wrapper">
                                    <i class="ri-check-line type-icon"></i>
                                </div>
                                <h6 class="type-card-title">{{ type.type_name }}</h6>
                            </div>
                            <div class="type-card-footer">
                                <span class="explore-text">Click to continue</span>
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
.types-page {
    background: linear-gradient(135deg, #f6f9fc 0%, #e8f0f8 100%);
    min-height: 100vh;
}

.types-hero {
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background: linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(21, 59, 112, 0.08);
}

.types-hero-content {
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.types-kicker {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #3f6c9e;
    font-weight: 700;
}

.types-title {
    color: #153b70;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
}

.types-text {
    color: #38506b;
    font-size: 1rem;
}

.types-stats {
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

.type-card {
    width: 100%;
    min-height: 180px;
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.type-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(21, 59, 112, 0.15);
}

.type-card:hover .type-card-body {
    background: linear-gradient(135deg, rgba(21, 59, 112, 0.03) 0%, rgba(34, 102, 168, 0.06) 100%);
}

.type-card:hover .type-card-footer {
    background: linear-gradient(90deg, #153b70, #2266a8);
}

.type-card:hover .type-card-footer .explore-text,
.type-card:hover .type-card-footer i {
    color: #ffffff;
}

.type-card:hover .type-icon {
    color: #153b70;
    transform: scale(1.1);
}

.type-card-body {
    padding: 28px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: #ffffff;
    transition: all 0.3s ease;
    min-height: 130px;
}

.type-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8f2ff 0%, #d0e4f8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.type-icon {
    font-size: 1.5rem;
    color: #2266a8;
    transition: all 0.3s ease;
}

.type-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #153b70;
    margin: 0;
    line-height: 1.3;
}

.type-card-footer {
    padding: 12px 16px;
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
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.type-card-footer i {
    color: #94a3b8;
    font-size: 0.9rem;
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
    .types-hero-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .types-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

