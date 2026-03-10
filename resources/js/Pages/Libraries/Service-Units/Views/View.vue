<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import VueMultiselect from 'vue-multiselect';
    import { router } from '@inertiajs/vue3';
    import { reactive, ref, watch } from 'vue';
    import QrcodeVue from 'qrcode.vue';
    import { Printd } from 'printd';
    import CSFPrint from '@/Pages/Libraries/Service-Units/Form/PrintCSF.vue';
    import AOS from 'aos';
    import 'aos/dist/aos.css';

    AOS.init();

    const props = defineProps({
        service: Object,
        unit: Object,
        unit_pstos: Object,
        sub_unit_pstos: Object,
        sub_unit_types: Object,
        user: Object,
    });

    const form = reactive({
        generated_url: null,
        selected_sub_unit: '',
        selected_unit_psto: '',
        selected_sub_unit_psto: '',
        sub_unit_type: '',
        client_type: ''
    });

    const qr_link_type = ref(null);
    const generated = ref(false);
    const copied = ref(false);
    const is_printing = ref(false);
    const baseURL = window.location.origin;

    const getSubUnitPSTO = async (sub_unit_id) => {
        const currentQueryParams = new URLSearchParams(window.location.search);
        currentQueryParams.set('sub_unit_id', sub_unit_id);
        const newUrl = `/csi/view?${currentQueryParams.toString()}`;

        await router.visit(newUrl, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    watch(
        () => form.selected_sub_unit,
        (value) => {
            getSubUnitPSTO(value.id);
        }
    );

    const generateURL = async (sub_unit, unit_psto, sub_unit_psto, sub_unit_type) => {
        generated.value = true;

        if (props.unit.data[0].id == 8) {
            qr_link_type.value = 4;
            form.generated_url = baseURL + '/services/csf?' +
                'region_id=' + props.user.region_id +
                '&service_id=' + props.service.id +
                '&unit_id=' + props.unit.data[0].id +
                '&client_type=' + form.client_type;
        } else if (unit_psto) {
            qr_link_type.value = 3;
            form.generated_url = baseURL + '/services/csf?' +
                'region_id=' + props.user.region_id +
                '&service_id=' + props.service.id +
                '&unit_id=' + props.unit.data[0].id +
                '&psto_id=' + unit_psto.id;
        } else if (sub_unit_psto && sub_unit_psto) {
            qr_link_type.value = 2;
            form.generated_url = baseURL + '/services/csf?' +
                'region_id=' + props.user.region_id +
                '&service_id=' + props.service.id +
                '&unit_id=' + props.unit.data[0].id +
                '&sub_unit_id=' + sub_unit.id +
                '&psto_id=' + sub_unit_psto.id;
        } else if (sub_unit) {
            if (sub_unit_type) {
                qr_link_type.value = 1.1;
                form.generated_url = baseURL + '/services/csf?' +
                    'region_id=' + props.user.region_id +
                    '&service_id=' + props.service.id +
                    '&unit_id=' + props.unit.data[0].id +
                    '&sub_unit_id=' + sub_unit.id +
                    '&sub_unit_type=' + sub_unit_type.type_name;
            } else {
                qr_link_type.value = 1.2;
                form.generated_url = baseURL + '/services/csf?' +
                    'region_id=' + props.user.region_id +
                    '&service_id=' + props.service.id +
                    '&unit_id=' + props.unit.data[0].id +
                    '&sub_unit_id=' + sub_unit.id;
            }
        } else {
            qr_link_type.value = 0;
            form.generated_url = baseURL + '/services/csf?' +
                'region_id=' + props.user.region_id +
                '&service_id=' + props.service.id +
                '&unit_id=' + props.unit.data[0].id;
        }
    };

    const copyToClipboard = () => {
        const textarea = document.createElement('textarea');
        textarea.value = form.generated_url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        copied.value = true;

        setTimeout(() => {
            copied.value = false;
        }, 2000);
    };

    const printCSFForm = async () => {
        is_printing.value = true;
        let d = await new Printd();
        let css = `
            @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;800&family=Roboto:wght@100;300;400;500;700;900&display=swap');
            * {
                font-family: 'Time New Roman'
            }
            .new-page {
                page-break-before: always;
            }
            .th-color{
                background-color: #8fd1e8;
            }
            .text-center{
                text-align: center;
            }
            .text-right{
                text-align: end
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            tr, th, td {
                border: 1px solid rgb(145, 139, 139);
                padding: 3px;
            }
            .page-break {
                page-break-before: always;
            }
            .form-control {
                display: block;
                width: 100%;
                padding: 0.375rem 0.75rem;
                font-size: 1rem;
                line-height: 1.5;
                color: #495057;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
        `;

        d.print(document.querySelector('.print-id'), [css]);
    };
</script>

<template>
    <AppLayout title="View Service Unit">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">View Service Unit</h2>
                <p class="page-heading-subtitle mb-0">Generate survey URL with QR code for selected service unit.</p>
            </div>
        </template>

        <div class="container-fluid py-4 view-service-unit-page">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-11">
                    <!-- Summary Hero -->
                    <div class="summary-hero mb-4" data-aos="fade-up">
                        <div class="summary-hero-content">
                            <div>
                                <p class="summary-kicker mb-1">Survey Link Generator</p>
                                <h3 class="summary-title mb-1">{{ service?.services_name || 'Service' }}</h3>
                                <p class="summary-text mb-0">
                                    Unit: {{ props.unit.data[0]?.unit_name || 'N/A' }}
                                </p>
                            </div>
                            <div class="summary-stats">
                                <div class="stat-pill">
                                    <span class="stat-label">Service ID</span>
                                    <span class="stat-value">{{ service?.id || 'N/A' }}</span>
                                </div>
                                <div class="stat-pill">
                                    <span class="stat-label">Unit ID</span>
                                    <span class="stat-value">{{ props.unit.data[0]?.id || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters Card -->
                    <div class="card filter-card shadow border-0 mb-4" data-aos="fade-up">
                        <div class="card-header filter-card-header text-white">
                            <h4 class="card-title mb-0 d-flex align-items-center">
                                <i class="ri-settings-2-line me-2"></i>
                                Generate Survey Link
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <!-- Sub Unit Selection -->
                                <div class="col-md-4" v-if="unit.data[0].sub_units && unit.data[0].sub_units.length > 0">
                                    <label class="form-label fw-semibold">Select Sub Unit</label>
                                    <vue-multiselect
                                        v-model="form.selected_sub_unit"
                                        :options="unit.data[0].sub_units || []"
                                        :multiple="false"
                                        placeholder="Select Sub Unit*"
                                        label="sub_unit_name"
                                        track-by="sub_unit_name"
                                        :allow-empty="false"
                                        class="custom-multiselect"
                                    >
                                    </vue-multiselect>
                                </div>

                                <!-- Unit PSTO Selection -->
                                <div class="col-md-4" v-if="unit_pstos.length > 0">
                                    <label class="form-label fw-semibold">Select Unit PSTO</label>
                                    <vue-multiselect
                                        v-model="form.selected_unit_psto"
                                        :options="unit_pstos"
                                        :multiple="false"
                                        placeholder="Select Unit PSTO"
                                        label="psto_name"
                                        track-by="psto_name"
                                        :allow-empty="false"
                                        class="custom-multiselect"
                                    >
                                    </vue-multiselect>
                                </div>

                                <!-- Sub Unit PSTO Selection -->
                                <div class="col-md-4" v-if="sub_unit_pstos.length > 0">
                                    <label class="form-label fw-semibold">Select Sub Unit PSTO</label>
                                    <vue-multiselect
                                        v-model="form.selected_sub_unit_psto"
                                        :options="sub_unit_pstos"
                                        :multiple="false"
                                        placeholder="Select Sub Unit PSTO"
                                        label="psto_name"
                                        track-by="psto_name"
                                        :allow-empty="false"
                                        class="custom-multiselect"
                                    >
                                    </vue-multiselect>
                                </div>

                                <!-- Sub Unit Type Selection -->
                                <div class="col-md-4" v-if="sub_unit_types.length > 0 && form.selected_sub_unit">
                                    <label class="form-label fw-semibold">Select Sub Unit Type</label>
                                    <vue-multiselect
                                        v-model="form.sub_unit_type"
                                        :options="sub_unit_types"
                                        :multiple="false"
                                        placeholder="Select Sub Unit Type"
                                        label="type_name"
                                        track-by="type_name"
                                        :allow-empty="false"
                                        class="custom-multiselect"
                                    >
                                    </vue-multiselect>
                                </div>

                                <!-- Generate Button -->
                                <div class="col-md-4 d-flex align-items-end">
                                    <button
                                        class="btn btn-primary w-100 generate-btn"
                                        :disabled="(unit.data[0].sub_units && unit.data[0].sub_units.length > 0 && form.selected_sub_unit == '') ||
                                            sub_unit_pstos.length > 0 && form.selected_sub_unit_psto == '' ||
                                            unit_pstos.length > 0 && form.selected_unit_psto == '' ||
                                            form.selected_sub_unit == 3 && form.sub_unit_type == ''"
                                        @click="generateURL(form.selected_sub_unit, form.selected_unit_psto, form.selected_sub_unit_psto, form.sub_unit_type)"
                                    >
                                        <i class="ri-link me-2"></i>
                                        Generate URL
                                    </button>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            <div class="active-filters mt-3">
                                <span class="filter-chip" v-if="form.selected_sub_unit">
                                    <strong>Sub Unit:</strong> {{ form.selected_sub_unit.sub_unit_name || '-' }}
                                </span>
                                <span class="filter-chip" v-if="form.selected_unit_psto">
                                    <strong>Unit PSTO:</strong> {{ form.selected_unit_psto.psto_name || '-' }}
                                </span>
                                <span class="filter-chip" v-if="form.selected_sub_unit_psto">
                                    <strong>Sub Unit PSTO:</strong> {{ form.selected_sub_unit_psto.psto_name || '-' }}
                                </span>
                                <span class="filter-chip" v-if="form.sub_unit_type">
                                    <strong>Type:</strong> {{ form.sub_unit_type.type_name || '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Preview Card -->
                    <div v-if="generated && form.generated_url" class="card mt-4 shadow border-0 report-preview-card" data-aos="fade-in">
                        <div class="card-header preview-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1 text-white">
                                    <i class="ri-qr-code-line me-2"></i>
                                    Generated Link & QR Code
                                </h5>
                                <p class="mb-0 preview-period text-white-50">Survey URL ready for distribution</p>
                            </div>
                            <button @click="copyToClipboard" class="btn btn-light preview-print-btn">
                                <i class="ri-file-copy-line me-2"></i>
                                {{ copied ? 'Copied!' : 'Copy URL' }}
                            </button>
                        </div>
                        <div class="card-body print-id">
                            <!-- URL Display -->
                            <div class="url-display mb-4">
                                <label class="form-label fw-semibold">Survey URL</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form.generated_url"
                                        readonly
                                        placeholder="Generated URL"
                                    />
                                    <button
                                        class="btn btn-outline-secondary"
                                        @click="copyToClipboard()"
                                        type="button"
                                    >
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- QR Code Display -->
                            <div class="qr-code-section text-center">
                                <div class="qr-code-container">
                                    <QrcodeVue
                                        v-if="qr_link_type == 0"
                                        :render-as="'svg'"
                                        :value="`${baseURL}/services/csf?region_id=${user.region_id}&service_id=${props.service.id}&unit_id=${props.unit.data[0].id}`"
                                        :size="200"
                                        :foreground="'#000'"
                                        level="L"
                                        style="border: 4px #ffffff solid; width: 250px; height: 250px;"
                                    />
                                    <QrcodeVue
                                        v-if="qr_link_type == 1.1"
                                        :render-as="'svg'"
                                        :value="`${baseURL}/services/csf?region_id=${user.region_id}&service_id=${props.service.id}&unit_idk=${unit.data[0].id }&sub_unit_id=${form.selected_sub_unit.id}`"
                                        :size="200"
                                        :foreground="'#000'"
                                        level="L"
                                        style="border: 4px #ffffff solid; width: 250px; height: 250px;"
                                    />
                                    <QrcodeVue
                                        v-if="qr_link_type == 1.2"
                                        :render-as="'svg'"
                                        :value="`${baseURL}/services/csf?region_id=${user.region_id}&service_id=${props.service.id}&unit_id=${unit.data[0].id }&sub_unit_id=${form.selected_sub_unit.id}&sub_unit_type=${form.sub_unit_type.type_name}`"
                                        :size="200"
                                        :foreground="'#000'"
                                        level="L"
                                        style="border: 4px #ffffff solid; width: 250px; height: 250px;"
                                    />
                                    <QrcodeVue
                                        v-if="qr_link_type == 2"
                                        :render-as="'svg'"
                                        :value="`${baseURL}/services/csf?region_id=${user.region_id}&service_id=${props.service.id}&unit_id=${unit.data[0].id }&sub_unit_id=${form.selected_sub_unit.id}&psto_id=${form.selected_sub_unit_psto.id}`"
                                        :size="200"
                                        :foreground="'#000'"
                                        level="L"
                                        style="border: 4px #ffffff solid; width: 250px; height: 250px;"
                                    />
                                    <QrcodeVue
                                        v-if="qr_link_type == 3"
                                        :render-as="'svg'"
                                        :value="`${baseURL}/services/csf?region_id=${user.region_id}&service_id=${props.service.id}&unit_id=${unit.data[0].id}&psto_id=${form.selected_unit_psto.id}`"
                                        :size="200"
                                        :foreground="'#000'"
                                        level="L"
                                        style="border: 4px #ffffff solid; width: 250px; height: 250px;"
                                    />
                                </div>
                                <p class="qr-hint mt-2 mb-0">Scan this QR code to access the survey form</p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="empty-state-card text-center mt-4" data-aos="fade-in">
                        <div class="empty-state-icon mb-2">
                            <i class="ri-qr-code-line"></i>
                        </div>
                        <h5 class="mb-1">No Link Generated Yet</h5>
                        <p class="mb-0">Select your options above, then click <strong>Generate URL</strong> to create a survey link with QR code.</p>
                    </div>
                </div>
            </div>
        </div>

        <CSFPrint
            v-if="generated == true"
            :is_printing="is_printing"
            :form="form"
            :data="props"
        />
    </AppLayout>
</template>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

<style scoped>
    .view-service-unit-page {
        --brand-navy: #153b70;
        --brand-blue: #2266a8;
        --brand-sky: #e9f3ff;
        --surface: #ffffff;
        --text-strong: #12243a;
        --text-soft: #5b7088;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }
    tr, th, td {
        border: 1px solid rgb(145, 139, 139);
        padding: 8px;
    }

    /* Page Heading */
    .page-heading-title {
        margin: 0;
        color: var(--text-strong);
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .page-heading-subtitle {
        margin-top: 2px;
        color: var(--text-soft);
        font-size: 0.9rem;
    }

    /* Summary Hero */
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
        gap: 18px;
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
        justify-content: flex-end;
    }

    .stat-pill {
        background: #ffffff;
        border: 1px solid #d3e4f8;
        border-radius: 12px;
        min-width: 118px;
        padding: 8px 12px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 8px rgba(27, 72, 122, 0.08);
    }

    .stat-label {
        color: #5f7893;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stat-value {
        color: #0d2f54;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Filter Card */
    .filter-card {
        border-radius: 14px;
        overflow: hidden;
    }

    .filter-card-header {
        background: linear-gradient(90deg, var(--brand-navy), var(--brand-blue));
        border-bottom: none;
    }

    .filter-card .card-body {
        background: var(--surface);
    }

    .filter-card .form-label {
        color: #1f3956;
        margin-bottom: 6px;
    }

    .filter-card :deep(.form-select) {
        border-color: #b8cfe8;
        min-height: 40px;
    }

    .filter-card :deep(.form-select:focus) {
        border-color: #3d89d1;
        box-shadow: 0 0 0 0.2rem rgba(29, 122, 208, 0.15);
    }

    /* Custom Multiselect */
    .custom-multiselect :deep(.multiselect__tags) {
        min-height: 40px;
        border-color: #b8cfe8;
    }

    .custom-multiselect :deep(.multiselect__tags-wrap) {
        padding-top: 4px;
    }

    .custom-multiselect :deep(.multiselect__single) {
        padding-top: 4px;
    }

    .custom-multiselect :deep(.multiselect__input),
    .custom-multiselect :deep(.multiselect__single) {
        font-size: 0.95rem;
    }

    /* Generate Button */
    .generate-btn {
        min-height: 40px;
        background: linear-gradient(135deg, #1f6db3, #1a4f89);
        border: none;
        font-weight: 700;
    }

    .generate-btn:hover {
        background: linear-gradient(135deg, #1a5d99, #153f6d);
    }

    .generate-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    /* Active Filters */
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-chip {
        background: var(--brand-sky);
        border: 1px solid #c7ddf4;
        border-radius: 999px;
        padding: 5px 10px;
        color: #1d466f;
        font-size: 0.78rem;
    }

    /* Report Preview Card */
    .report-preview-card {
        border-radius: 14px;
        overflow: hidden;
    }

    .preview-header {
        background: linear-gradient(90deg, #1b365d, #2d4f81);
        border-bottom: none;
    }

    .preview-period {
        font-size: 0.82rem;
    }

    .preview-print-btn {
        font-weight: 700;
        border: none;
    }

    /* URL Display */
    .url-display .form-label {
        color: #1f3956;
        margin-bottom: 6px;
    }

    .url-display .form-control {
        border-color: #b8cfe8;
    }

    .url-display .form-control:focus {
        border-color: #3d89d1;
        box-shadow: 0 0 0 0.2rem rgba(29, 122, 208, 0.15);
    }

    /* QR Code Section */
    .qr-code-section {
        padding: 20px;
    }

    .qr-code-container {
        display: inline-block;
        padding: 15px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(27, 72, 122, 0.15);
    }

    .qr-hint {
        color: var(--text-soft);
        font-size: 0.9rem;
    }

    /* Empty State */
    .empty-state-card {
        border: 1px dashed #bad1e8;
        border-radius: 14px;
        padding: 26px 16px;
        background: #f7fbff;
        color: #2d4e6e;
    }

    .empty-state-icon {
        font-size: 2rem;
        color: #35689a;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .summary-hero-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .summary-stats {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

