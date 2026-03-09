<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive, ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Printd } from "printd";
import Swal from 'sweetalert2';
import MonthlyContent from '@/Pages/CSI/AllServicesUnits/Monthly/Content.vue';
import VueMultiselect from "vue-multiselect";
import AOS from 'aos'
import 'aos/dist/aos.css'

AOS.init();
const props = defineProps({
  services_units: Object,
  cc_data: Object,
  all_units_data: Object,
  csi_total: Number,
  nps_total: Number,
  lsr_total: Number,
  total_respondents: Number,
  total_vss_respondents: Number,
  percentage_vss_respondents: Number,
  respondent_profile: Object,
  request: Object,
});


const form = reactive({
  date_from: null,
  date_to: null,
  csi_type: null,
  selected_month: null,
  selected_year: null,
  selected_quarter: null,
  client_type: null,
  sex: null,
  age_group: null,
  comments_complaints: null,
  analysis: null,

  service: [],
})

//get year
const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const last9Years = Array.from({ length: 9 }, (_, index) => (currentYear - index).toString());
    return last9Years;
});

const months = [
    'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL',
    'MAY', 'JUNE', 'JULY', 'AUGUST',
    'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
];
const clientTypes = ['Internal', 'External'];
const sexOptions = ['Male', 'Female', 'Prefer not to say'];
const ageGroupOptions = ['19 or lower', '20-34', '35-49', '50-64', '60+', 'Prefer not to say'];

const currentYear = ref(getCurrentYear());

function getCurrentYear() {
    return new Date().getFullYear().toString();
}

//get month
const currentMonth = ref(getCurrentMonth());

function getCurrentMonth() {
    const currentDate = new Date();
    return months[currentDate.getMonth()];
}

const generated = ref(false); 
onMounted(() => {
    const previousRequest = props.request || {};
    form.csi_type = previousRequest.csi_type || null;
    form.selected_month = previousRequest.selected_month || currentMonth.value;
    form.selected_year = previousRequest.selected_year || currentYear.value;
    form.selected_quarter = previousRequest.selected_quarter || null;
    form.client_type = previousRequest.client_type || null;
    form.sex = previousRequest.sex || null;
    form.age_group = previousRequest.age_group || null;
    generated.value = Boolean(previousRequest.csi_type);
});

const reportPeriodLabel = computed(() => {
    if (form.csi_type === 'By Quarter' && form.selected_quarter) {
        return `${form.selected_quarter} ${form.selected_year || ''}`.trim();
    }
    if (form.csi_type === 'By Year/Annual') {
        return form.selected_year || '';
    }
    return `${form.selected_month || ''} ${form.selected_year || ''}`.trim();
});


const generateCSIReport = async () => {
    if (!form.csi_type) {
        Swal.fire({
            title: "Error",
            icon: "error",
            text: "Please select a report type first!"
        });
        generated.value = false;
        return;
    }

    generated.value = true;

    if(form.csi_type == 'By Month'){
          form.selected_quarter = "";
          router.get('/csi/generate/all-units/monthly', form , { preserveState: true, preserveScroll: true})
    }
    else if(form.csi_type == 'By Quarter'){
          form.selected_month = "";
          if(form.selected_quarter){
              router.get('/csi/generate/all-units/monthly', form , { preserveState: true, preserveScroll: true})
          }
          else{ 
            generated.value = false;
            Swal.fire({
                  title: "Error",
                  icon: "error",
                  text: "Please select a quarter first!"           
              });
          }
    }
      else if(form.csi_type == 'By Year/Annual'){
          form.selected_quarter = "";
          if(form.selected_year ){
             router.get('/csi/generate/all-units/monthly', form , { preserveState: true, preserveScroll: true})
          }
          else{         
              generated.value = false;
              Swal.fire({
                  title: "Error",
                  icon: "error",
                  text: "Please select year first!"           
              });
          }     
      }

    
  };


  const is_printing = ref(false);
  const printCSIReport = async () => {
      is_printing.value = true;
      //  router.get('/generate-pdf', form , { preserveState: true, preserveScroll: true})
      //Create an instance of Printd
        let d = await new Printd();
        let css = ` 
          @page {
            size: A4 portrait;
            margin: 10mm;
          }
          * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          body {
            margin: 0;
            color: #111827;
            font-size: 11px;
            line-height: 1.3;
          }
          h4, h5 {
            margin: 0 0 8px 0;
            color: #1f2937;
          }
          .m-5 {
            margin: 0 !important;
          }
          .mb-3 {
            margin-bottom: 12px !important;
          }
          .mb-4 {
            margin-bottom: 14px !important;
          }
          .mt-4 {
            margin-top: 14px !important;
          }
          .text-center {
            text-align: center !important;
          }
          .text-right {
            text-align: right !important;
          }
          .text-left {
            text-align: left !important;
          }
          .pl-5, .pl-10, .pl-14 {
            padding-left: 8px !important;
          }

          table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
          }
          th, td {
            border: 1px solid #9ca3af;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
          }
          thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700;
          }
          .bg-blue-200 {
            background-color: #e3f2fd !important;
          }
          .bg-yellow-50 {
            background-color: #fef9e7 !important;
          }
          .bg-green-50 {
            background-color: #e8f5e9 !important;
          }
          .total-row {
            font-weight: 700;
            background-color: #eef2ff !important;
          }
          .assessment {
            margin-top: 10px !important;
          }
          .assessment p {
            margin: 0 0 6px 0;
          }

          .pie-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
          }
          .pie-chart-collapsible {
            display: grid !important;
          }
          .pie-toggle-btn,
          .pie-collapsed-note {
            display: none !important;
          }
          .pie-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            page-break-inside: avoid;
          }
          .service-category-summary {
            page-break-inside: avoid;
          }
          .service-category-summary table {
            width: 100% !important;
            table-layout: fixed !important;
            border: 1px solid #64748b !important;
          }
          .service-category-summary th,
          .service-category-summary td {
            border: 1px solid #94a3b8 !important;
            padding: 4px 3px !important;
            font-size: 9px !important;
            line-height: 1.2 !important;
            text-align: center !important;
            vertical-align: middle !important;
            word-break: keep-all !important;
            white-space: normal !important;
          }
          .service-category-summary thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
          }
          .service-category-summary tr > th:nth-child(1),
          .service-category-summary tr > td:nth-child(1) {
            width: 25% !important;
            text-align: left !important;
            font-weight: 700 !important;
          }
          .service-category-summary tr > th:nth-child(2),
          .service-category-summary tr > td:nth-child(2),
          .service-category-summary tr > th:nth-child(3),
          .service-category-summary tr > td:nth-child(3),
          .service-category-summary tr > th:nth-child(4),
          .service-category-summary tr > td:nth-child(4),
          .service-category-summary tr > th:nth-child(5),
          .service-category-summary tr > td:nth-child(5) {
            width: 18.75% !important;
          }
          .pie-title {
            font-size: 12px;
            font-weight: 700;
            text-align: center;
          }
          .pie-subtitle {
            font-size: 10px;
            text-align: center;
            color: #334155;
            margin-bottom: 6px;
          }
          .pie-circle {
            width: 120px !important;
            height: 120px !important;
            border-radius: 50%;
            margin: 0 auto 8px auto;
            border: 1px solid #94a3b8;
          }
          .pie-total {
            text-align: center;
            font-size: 10px;
            margin-bottom: 6px;
          }
          .pie-legend-table th,
          .pie-legend-table td {
            font-size: 9px !important;
            padding: 3px !important;
          }
          .legend-label {
            display: flex;
            align-items: center;
            gap: 4px;
          }
          .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
          }

          .new-page,
          .page-break {
            page-break-before: always;
          }
        `;

       d.print(document.querySelector(".print-id"), [css]);
};

</script>

<template>
    <AppLayout title="Customer Satisfaction Index">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Customer Satisfaction Index - All Services Units</h2>
                <p class="page-heading-subtitle mb-0">Generate monthly, quarterly, or yearly consolidated reports with profile filters.</p>
            </div>
        </template>

        <div class="container-fluid py-4 csi-all-units-page">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-11">
                    <div class="summary-hero mb-4" data-aos="fade-up">
                        <div class="summary-hero-content">
                            <div>
                                <p class="summary-kicker mb-1">Customer Experience Analytics</p>
                                <h3 class="summary-title mb-1">All Services Units Dashboard</h3>
                                <p class="summary-text mb-0">
                                    Build a consolidated CSI snapshot by period, then print a formatted report.
                                </p>
                            </div>
                            <div class="summary-stats">
                                <div class="stat-pill">
                                    <span class="stat-label">CSI</span>
                                    <span class="stat-value">{{ Number(props.csi_total || 0).toFixed(2) }}%</span>
                                </div>
                                <div class="stat-pill">
                                    <span class="stat-label">NPS</span>
                                    <span class="stat-value">{{ Number(props.nps_total || 0).toFixed(2) }}%</span>
                                </div>
                                <div class="stat-pill">
                                    <span class="stat-label">Respondents</span>
                                    <span class="stat-value">{{ props.total_respondents ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters Card -->
                    <div class="card filter-card shadow border-0 mb-4" data-aos="fade-up">
                        <div class="card-header filter-card-header text-white">
                            <h4 class="card-title mb-0 d-flex align-items-center">
                                <i class="ri-filter-3-line me-2"></i>
                                Generate Report
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Report Type</label>
                                    <select v-model="form.csi_type" class="form-select">
                                        <option value="">Select Report Type</option>
                                        <option value="By Month">By Month</option>
                                        <option value="By Quarter">By Quarter</option>
                                        <option value="By Year/Annual">By Year/Annual</option>
                                    </select>
                                </div>
                                <div class="col-md-4" v-if="form.csi_type == 'By Month'">
                                    <label class="form-label fw-semibold">Month</label>
                                    <select v-model="form.selected_month" class="form-select">
                                        <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4" v-if="form.csi_type == 'By Quarter'">
                                    <label class="form-label fw-semibold">Quarter</label>
                                    <select v-model="form.selected_quarter" class="form-select">
                                        <option value="">Select Quarter</option>
                                        <option value="FIRST QUARTER">First Quarter</option>
                                        <option value="SECOND QUARTER">Second Quarter</option>
                                        <option value="THIRD QUARTER">Third Quarter</option>
                                        <option value="FOURTH QUARTER">Fourth Quarter</option>
                                    </select>
                                </div>
                                <div class="col-md-4" v-if="form.csi_type">
                                    <label class="form-label fw-semibold">Year</label>
                                    <select v-model="form.selected_year" class="form-select">
                                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Client Type</label>
                                    <select v-model="form.client_type" class="form-select">
                                        <option :value="null">All</option>
                                        <option v-for="item in clientTypes" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sex</label>
                                    <select v-model="form.sex" class="form-select">
                                        <option :value="null">All</option>
                                        <option v-for="item in sexOptions" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Age Group</label>
                                    <select v-model="form.age_group" class="form-select">
                                        <option :value="null">All</option>
                                        <option v-for="item in ageGroupOptions" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button @click="generateCSIReport" class="btn btn-primary w-100 generate-btn">
                                        <i class="ri-file-chart-line me-2"></i>
                                        Generate Report
                                    </button>
                                </div>
                            </div>

                            <div class="active-filters mt-3">
                                <span class="filter-chip">
                                    <strong>Type:</strong> {{ form.csi_type || 'Not selected' }}
                                </span>
                                <span class="filter-chip" v-if="form.csi_type === 'By Month'">
                                    <strong>Month:</strong> {{ form.selected_month || '-' }}
                                </span>
                                <span class="filter-chip" v-if="form.csi_type === 'By Quarter'">
                                    <strong>Quarter:</strong> {{ form.selected_quarter || '-' }}
                                </span>
                                <span class="filter-chip">
                                    <strong>Year:</strong> {{ form.selected_year || '-' }}
                                </span>
                                <span class="filter-chip">
                                    <strong>Client:</strong> {{ form.client_type || 'All' }}
                                </span>
                                <span class="filter-chip">
                                    <strong>Sex:</strong> {{ form.sex || 'All' }}
                                </span>
                                <span class="filter-chip">
                                    <strong>Age:</strong> {{ form.age_group || 'All' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Content Preview Card -->
                    <div v-if="generated == true && form.csi_type" class="card mt-4 shadow border-0 report-preview-card" data-aos="fade-in">
                        <div class="card-header preview-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1 text-white">
                                    <i class="ri-file-chart-line me-2"></i>
                                    Report Preview
                                </h5>
                                <p class="mb-0 preview-period text-white-50">{{ reportPeriodLabel }}</p>
                            </div>
                            <button @click="printCSIReport" class="btn btn-light preview-print-btn">
                                <i class="ri-printer-line me-2"></i>
                                Print Report
                            </button>
                        </div>
                        <div class="card-body print-id">
                            <MonthlyContent :form="form" :data="{
                                services_units: props.services_units,
                                all_units_data: props.all_units_data,
                                cc_data: props.cc_data,
                                total_respondents: props.total_respondents,
                                total_vss_respondents: props.total_vss_respondents,
                                percentage_vss_respondents: props.percentage_vss_respondents,
                                respondent_profile: props.respondent_profile,
                                csi_total: props.csi_total,
                                nps_total: props.nps_total,
                                lsr_total: props.lsr_total
                            }" />
                        </div>
                    </div>

                    <div v-else class="empty-state-card text-center mt-4" data-aos="fade-in">
                        <div class="empty-state-icon mb-2">
                            <i class="ri-file-chart-line"></i>
                        </div>
                        <h5 class="mb-1">No Report Preview Yet</h5>
                        <p class="mb-0">Choose your filters above, then click <strong>Generate Report</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style scoped>
.csi-all-units-page {
    --brand-navy: #153b70;
    --brand-blue: #2266a8;
    --brand-sky: #e9f3ff;
    --surface: #ffffff;
    --text-strong: #12243a;
    --text-soft: #5b7088;
}

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

.filter-card .form-select {
    border-color: #b8cfe8;
    min-height: 40px;
}

.filter-card .form-select:focus {
    border-color: #3d89d1;
    box-shadow: 0 0 0 0.2rem rgba(29, 122, 208, 0.15);
}

.generate-btn {
    min-height: 40px;
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border: none;
    font-weight: 700;
}

.generate-btn:hover {
    background: linear-gradient(135deg, #1a5d99, #153f6d);
}

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
