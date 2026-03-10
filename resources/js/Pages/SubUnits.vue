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
    unit: Object,
    sub_units:Object,
});

const goNext = async (region_id, service_id, unit_id, sub_unit_id) => {
    if(sub_unit_id == 3){
        router.get(`/services/csf/sub-unit/types?region_id=`+ region_id + 
                                `&service_id=`+ service_id + 
                                `&unit_id=`+ unit_id +
                                `&sub_unit_id=` + sub_unit_id );   
    }
    else{
        router.get(`/services/csf/sub-unit/pstos?region_id=`+ region_id + 
                                `&service_id=`+ service_id + 
                                `&unit_id=`+ unit_id +
                                `&sub_unit_id=` + sub_unit_id );      
    }
}

const goBack = async () => {
    window.history.back()
}

</script>

<template>
    <Head title="Service Units" />   
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" data-aos="fade-down" data-aos-duration="500" data-aos-delay="500" style="backdrop-filter: blur(2px);">
        <div class="container-fluid">
            <a href="/" class="navbar-brand d-flex align-items-center">
                <img src="../../../public/images/dost-logo.jpg" class="me-3" alt="DOST Logo" style="height: 2rem;">
                <span class="fw-bold fs-4">DOST <span v-if="region">{{ region.code }}</span> Customer Relation Management System</span>
            </a>
        </div>
    </nav>  
    <div class="min-vh-100 d-flex flex-column subunits-page">
        <div class="mx-3" style="margin-top: 100px;">
            <div class="subunits-hero" data-aos="fade-up">
                <div class="subunits-hero-content">
                    <div>
                        <p class="subunits-kicker mb-1">Available</p>
                        <h2 class="subunits-title mb-1">{{ unit?.unit_name || 'Sub Units' }}</h2>
                        <p class="subunits-text mb-0">Select a sub unit to continue</p>
                    </div>
                    <div class="subunits-stats">
                        <div class="stat-pill">
                            <span class="stat-label">Sub Units</span>
                            <span class="stat-value">{{ sub_units?.length || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div v-for="(sub_unit, index) in sub_units" :key="sub_unit.id" class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4" :data-aos="'zoom-in'" :data-aos-delay="index * 10">
                    <Link :href="sub_unit.id == 3 ? '/services/csf/sub-unit/types?region_id=' + region_id + '&service_id=' + service_id + '&unit_id=' + unit_id + '&sub_unit_id=' + sub_unit.id : '/services/csf/sub-unit/pstos?region_id=' + region_id + '&service_id=' + service_id + '&unit_id=' + unit_id + '&sub_unit_id=' + sub_unit.id" class="text-decoration-none">
                        <div class="subunit-card">
                            <div class="subunit-card-body">
                                <div class="subunit-icon-wrapper">
                                    <i class="ri-check-line subunit-icon"></i>
                                </div>
                                <h6 class="subunit-card-title">{{ sub_unit.sub_unit_name }}</h6>
                            </div>
                            <div class="subunit-card-footer">
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
.subunits-page {
    background: linear-gradient(135deg, #f6f9fc 0%, #e8f0f8 100%);
    min-height: 100vh;
}

.subunits-hero {
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background: linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(21, 59, 112, 0.08);
}

.subunits-hero-content {
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.subunits-kicker {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #3f6c9e;
    font-weight: 700;
}

.subunits-title {
    color: #153b70;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
}

.subunits-text {
    color: #38506b;
    font-size: 1rem;
}

.subunits-stats {
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

.subunit-card {
    width: 100%;
    min-height: 180px;
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.subunit-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(21, 59, 112, 0.15);
}

.subunit-card:hover .subunit-card-body {
    background: linear-gradient(135deg, rgba(21, 59, 112, 0.03) 0%, rgba(34, 102, 168, 0.06) 100%);
}

.subunit-card:hover .subunit-card-footer {
    background: linear-gradient(90deg, #153b70, #2266a8);
}

.subunit-card:hover .subunit-card-footer .explore-text,
.subunit-card:hover .subunit-card-footer i {
    color: #ffffff;
}

.subunit-card:hover .subunit-icon {
    color: #153b70;
    transform: scale(1.1);
}

.subunit-card-body {
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

.subunit-icon-wrapper {
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

.subunit-icon {
    font-size: 1.5rem;
    color: #2266a8;
    transition: all 0.3s ease;
}

.subunit-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #153b70;
    margin: 0;
    line-height: 1.3;
}

.subunit-card-footer {
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

.subunit-card-footer i {
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
    .subunits-hero-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .subunits-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

