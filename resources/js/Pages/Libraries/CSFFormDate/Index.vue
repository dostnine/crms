<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { reactive ,ref, watch, onMounted} from 'vue';
    import Swal from 'sweetalert2';
    
    const props = defineProps({
        date_display: String, 
    });

    const show_modal = ref(false);
    
    const showDateDisplayModal = async (is_show) => {
        show_modal.value = is_show;
    };

    const reloadDateDisplay = async () => {
        date_display.value = {};
    };

    watch(
        () => props.date_display[0].is_displayed,
        (value) => {
            router.post('/show-date-csf-form/update', { value } );
        }
    );

</script>


<template>
    <AppLayout title="CSF Form Date Display">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">CSF Form Date Display</h2>
                <p class="page-heading-subtitle mb-0">Control the visibility of the CSF form date.</p>
            </div>
        </template>

        <div class="container-fluid py-4 libraries-page">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-6">
                    <div class="card shadow-lg border-0 text-center" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #9c27b0 0%, #673ab7 50%, #3f51b5 100%); padding: 25px 30px;">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="ri-calendar-line" style="font-size: 100px;"></i>
                            </div>
                            <h3 class="card-title mb-0 position-relative">
                                <i class="ri-calendar-line me-2"></i>
                                CSF Form Date Display Settings
                            </h3>
                            <p class="mb-0 mt-2 opacity-75 position-relative" style="font-size: 0.9rem;">Toggle the CSF form date visibility</p>
                        </div>

                        <div class="card-body p-5">
                            <div class="mb-4">
                                <div class="icon-circle mx-auto" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, rgba(156, 39, 176, 0.2) 0%, rgba(103, 58, 183, 0.2) 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-calendar-event-line ri-3x text-purple"></i>
                                </div>
                            </div>
                            <h5 class="card-title mb-4 fw-bold">Is Displayed?</h5>
                            
                            <div class="form-check form-switch d-flex justify-content-center align-items-center mb-4">
                                <input 
                                    class="form-check-input fs-2 custom-switch" 
                                    type="checkbox" 
                                    role="switch" 
                                    id="displaySwitch"
                                    v-model="date_display[0].is_displayed"
                                    :true-value="1"
                                    :false-value="0"
                                >
                                <label class="form-check-label ms-3 fs-5 fw-semibold" for="displaySwitch" :class="date_display[0].is_displayed ? 'text-success' : 'text-muted'">
                                    {{ date_display[0].is_displayed ? 'Currently Showing' : 'Currently Hidden' }}
                                </label>
                            </div>

                            <div class="mt-4">
                                <button @click="showDateDisplayModal(true)" class="btn btn-primary btn-lg px-5" style="border-radius: 25px;">
                                    <i class="ri-edit-line me-2"></i> Update Settings
                                </button>
                            </div>
                        </div>

                        <div class="card-footer bg-light" style="border-radius: 0 0 20px 20px;">
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i>
                                Toggle the switch to show or hide the CSF form date on the interface
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.libraries-page {
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

.card {
    border: 1px solid #d8e5f5 !important;
    box-shadow: 0 10px 26px rgba(21, 59, 112, 0.08) !important;
}

.card-header {
    background: linear-gradient(90deg, #1b365d, #2a568f) !important;
}

.btn-primary {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border: none;
}
</style>
