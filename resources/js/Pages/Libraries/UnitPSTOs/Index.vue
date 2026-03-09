<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import Pagination from '@/Shared/Pagination.vue';
    import ModalForm from '@/Pages/Libraries/UnitPSTOs/Modal.vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { reactive ,ref, watch, onMounted} from 'vue';
    import Swal from 'sweetalert2';
    
    
    const props = defineProps({
        units: Object, 
        unit_pstos: Object, 
        pstos: Object,
    });


    const show_modal = ref(false);
    const action_clicked = ref(null);

    const form = ref({});
    const unit = ref({});
    const search = ref('');
    const loading = ref(false);

    watch(
    () => search.value,
        (search) => {
            loading.value = true;
            router.get('/unit-pstos', { search },{ preserveState: true, onFinish: () => { loading.value = false; } })
        }
        
    );
    
    const showUnitPSTOModal = async (is_show, action,data) => {
        show_modal.value = is_show;
        action_clicked.value = action;
        unit.value = data;
        
    };

    const deleteRecord = async (data) => {
        Swal.fire({
            html: '<div style="font-weight: bold; font-size:25px">Are you sure you want to delete this record?</div> ',
            icon:'warning',
            
            showCancelButton: true,
            confirmButtonText: "Yes, I'm sure",
            showLoaderOnConfirm: true,
        }).then((result) => {
            if (result.isConfirmed) {            
                router.post('/unit-pstos/delete', data );
            }
        });
    };

    const reloadUnits = async () => {
        unit.value = {};
    };

    let page_number = 1;
    const getUnits = async (page) => {
       router.visit('/unit-pstos?page=' + page , { preserveState: true});
       page_number = page;
    };

    const handleModalInput = (value) => {
        showUnitPSTOModal(value, action_clicked.value, unit.value);
    };
</script>


<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Unit PSTOs</h2>
                <p class="page-heading-subtitle mb-0">Assign PSTOs to service units.</p>
            </div>
        </template>

        <div class="container-fluid py-4 libraries-page">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-11">
                    <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header text-white position-relative overflow-hidden d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #6B8DD6 100%); padding: 20px 25px;">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="ri-building-line" style="font-size: 80px;"></i>
                            </div>
                            <div class="position-relative">
                                <h3 class="card-title mb-0">
                                    <i class="ri-building-line me-2"></i>
                                    Unit PSTOs Management
                                </h3>
                                <p class="mb-0 mt-1 opacity-75" style="font-size: 0.9rem;">Manage unit PSTO assignments</p>
                            </div>
                            <div class="d-flex gap-3 align-items-center">
                                <div class="search-box">
                                    <div class="input-group input-group-sm" style="border-radius: 25px; overflow: hidden;">
                                        <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                                        <input type="text" class="form-control border-0 shadow-none" placeholder="Search units..." v-model="search" style="border-radius: 0 25px 25px 0;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="min-width: 600px;">
                                    <thead class="table-dark" style="background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);">
                                        <tr>
                                            <th class="text-start" style="width: 60px;">#</th>
                                            <th class="text-left">Unit Name</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr 
                                            v-for="(unit,index) in units.data"
                                            :key="unit.id"
                                            class="align-middle table-row-animated"
                                        >
                                            <template v-if="unit">
                                                <td class="fw-bold text-muted">{{ index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-circle me-2" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%); display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-building-line text-purple"></i>
                                                        </div>
                                                        <span class="fw-semibold text-dark">{{ unit.unit_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button @click="showUnitPSTOModal(true, 'Assign' , unit)" class="btn btn-primary btn-sm" style="border-radius: 20px;">     
                                                        <i class="ri-add-circle-line me-1"></i> Assign                         
                                                    </button>
                                                </td>
                                            </template>
                                            <template v-else>
                                                <td colspan="3"> 
                                                    <div class="text-center py-4">
                                                        <i class="ri-inbox-line text-muted" style="font-size: 40px;"></i>
                                                        <p class="text-muted mb-0 mt-2">No data at the moment</p>
                                                    </div>
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer bg-light" style="border-radius: 0 0 20px 20px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="text-muted">
                                        Showing <span class="fw-semibold">{{ units.from }}</span> to <span class="fw-semibold">{{ units.to }}</span> out of
                                        <span class="fw-bold" style="color: #667eea;">{{ units.total }}</span> records
                                    </span>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li v-for="page in units.last_page" :key="page" class="page-item" :class="{ active: page === units.current_page }">
                                                <a class="page-link" href="#" @click.prevent="getUnits(page)">{{ page }}</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ModalForm 
            :value="show_modal"
            :unit="unit"
            :unit_pstos="unit_pstos"
            :pstos="pstos"
            :action="action_clicked"
            :page_number="page_number"
            @input="showUnitPSTOModal"
            @reloadUnits="reloadUnits"
        ></ModalForm>
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

.search-box .input-group {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border-color: #1f6db3;
}
</style>
