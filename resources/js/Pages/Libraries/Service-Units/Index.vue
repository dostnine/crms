<script setup>
    import VueMultiselect from 'vue-multiselect'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import AddServiceModal from '@/Components/AddServiceModal.vue'
    import { Head, Link, router } from '@inertiajs/vue3'
    import { reactive, ref, watch } from 'vue'

    const props = defineProps({
        service_units: Object,
        sub_units: Object,
        user: Object,
    })

    const form = reactive({
        service_id: null,
        unit_id: null,
    })

    const show_modal = ref(false)
    const action_clicked = ref('')
    const selected_service = ref({})

    const rating = async (service_id, unit_id) => {
        form.service_id = service_id
        form.unit_id = unit_id
        router.get('/csi', form, { preserveState: true })
    }

    const all_service_unit_rating = async () => {
        form.form_type = 'all units'
        router.get('/csi/all-units', form, { preserveState: true })
    }

    const goViewPage = async (service_id, unit_id) => {
        form.service_id = service_id
        form.unit_id = unit_id
        router.get('/csi/view', form, { preserveState: true })
    }

    const showServiceModal = async (is_show, action, service) => {
        show_modal.value = is_show
        action_clicked.value = action
        if (service) {
            selected_service.value = service
        }
    }

    const openPDF = () => {
        const pdfPath = 'https://drive.google.com/file/d/1s7hgXu2_3znCrcKrXX0PWJUQfwb7SMWU/view?usp=sharing'
        window.open(pdfPath, '_blank')
    }
</script>

<template>
    <AppLayout title="Service Units">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Service Units</h2>
                <p class="page-heading-subtitle mb-0">Manage service units and launch rating/report actions.</p>
            </div>
        </template>

        <div class="container-fluid py-4 libraries-page">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-11">
                    <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header text-white position-relative overflow-hidden d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); padding: 20px 25px;">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="ri-building-4-line" style="font-size: 80px;"></i>
                            </div>
                            <div class="position-relative">
                                <h3 class="card-title mb-0">
                                    <i class="ri-building-4-line me-2"></i>
                                    Service Units Management
                                </h3>
                                <p class="mb-0 mt-1 opacity-75" style="font-size: 0.9rem;">Configure and manage service units</p>
                            </div>
                            <div class="d-flex gap-3 align-items-center">
                                <button
                                    v-if="user.account_type == 'admin'"
                                    @click="showServiceModal(true, 'add_new_service', null)"
                                    class="btn btn-light btn-sm fw-semibold"
                                    style="border-radius: 20px;"
                                >
                                    <i class="ri-add-line me-1"></i>
                                    Add New Service
                                </button>
                                <button
                                    @click="all_service_unit_rating()"
                                    class="btn btn-warning btn-sm fw-semibold"
                                    style="border-radius: 20px;"
                                >
                                    <i class="ri-file-chart-line me-1"></i>
                                    All Unit Ratings
                                </button>
                                <button
                                    @click="openPDF()"
                                    class="btn btn-success btn-sm fw-semibold"
                                    style="border-radius: 20px;"
                                >
                                    <i class="ri-printer-line me-1"></i>
                                    CSF Form (Manual)
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="min-width: 600px;">
                                    <thead class="table-dark" style="background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">#</th>
                                            <th>Unit Name</th>
                                            <th class="text-center" style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template
                                            v-if="service_units"
                                            v-for="(service_unit, serviceIndex) in service_units.data"
                                            :key="service_unit.id"
                                        >
                                            <!-- Service Header Row -->
                                            <tr class="table-primary service-header-row">
                                                <td colspan="3" class="fw-bold fs-5">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-circle me-2" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, rgba(79, 172, 254, 0.3) 0%, rgba(0, 242, 254, 0.3) 100%); display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-service-line text-info"></i>
                                                        </div>
                                                        <span>{{ service_unit.services_name }}</span>
                                                        <button
                                                            v-if="user.account_type == 'admin'"
                                                            @click="showServiceModal(true, 'add_new_unit', service_unit)"
                                                            class="btn btn-success btn-sm ms-3"
                                                            style="border-radius: 15px;"
                                                        >
                                                            <i class="ri-add-line"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Unit Rows -->
                                            <tr
                                                v-for="(unit, unitIndex) in service_unit.units"
                                                :key="unit.id"
                                                class="align-middle table-row-animated"
                                            >
                                                <td class="text-center fw-bold text-muted">
                                                    {{ unitIndex + 1 }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-circle me-2" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(79, 172, 254, 0.2) 0%, rgba(0, 242, 254, 0.2) 100%); display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-building-line text-blue"></i>
                                                        </div>
                                                        <span class="fw-semibold text-dark">{{ unit.unit_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button
                                                            @click="goViewPage(service_unit.id, unit.id)"
                                                            class="btn btn-primary"
                                                            style="border-radius: 15px 0 0 15px;"
                                                            :disabled="user.account_type == 'user' && user.unit_id != unit.id"
                                                        >
                                                            <i class="ri-eye-line me-1"></i>
                                                            View
                                                        </button>
                                                        <button
                                                            @click="rating(service_unit.id, unit.id)"
                                                            class="btn btn-warning"
                                                            style="border-radius: 0 15px 15px 0;"
                                                            :disabled="user.account_type == 'user' && user.unit_id != unit.id"
                                                        >
                                                            <i class="ri-star-line me-1"></i>
                                                            Rating
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AddServiceModal
            :value="show_modal"
            :action-clicked="action_clicked"
            :selected-service="selected_service"
            @input="showServiceModal"
        />
    </AppLayout>
</template>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

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

.service-header-row {
    background: #eaf2ff !important;
}

.table-dark th {
    background: #1f3b6e !important;
    border-color: #365988 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(33, 94, 154, 0.06) !important;
}

.btn-primary {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg, #c47b2f, #a65c27);
    border: none;
    color: #fff;
}

.btn-success {
    background: linear-gradient(135deg, #2e9b6a, #1f7f67);
    border: none;
}
</style>
