<script setup lang="ts">
import { reactive, watch, ref, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from '@inertiajs/vue3';
import VueMultiselect from "vue-multiselect";
import ByUnitMonthlyReport from '@/Pages/CSI/Monthly/ByUnitMonthly.vue';
import { Printd } from "printd";
const emit = defineEmits(["input"]);
const props = defineProps({
    form: {
        type: Object,
        default: null,
    },
    assignatorees: {
        type: Object,
        default: null,
    },
    user: {
        type: Object,
        default: null,
    },

    users: {
        type: Object,
        default: null,
    },

    value: {
        type: Boolean,
        default: false,
    },
    data: {
        type: Object,
        default: null,
    },
    generated:{
        type: Boolean,
    }



});


const show_form_modal = ref(false);
watch(
    () => props.value,
    (value) => {
        show_form_modal.value = value;
    }
);


const form_assignatorees = reactive({
    prepared_by: props.user,
    noted_by:{},
});




// Removed unused function

const closeDialog = (value) => {
    emit("input", value);
};


  const is_printing = ref(false);
  const printCSIReport = async () => {
      is_printing.value = true;
      //  router.get('/generate-pdf', form , { preserveState: true, preserveScroll: true})
      //Create an instance of Printd
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
            text-align:end
          }
          table {
            border-collapse: collapse;
            width: 100%; /* Optional: Set a width for the table */
          }

          tr, th, td {
            border: 1px solid rgb(145, 139, 139); /* Optional: Add a border for better visibility */
            padding: 3px; /* Optional: Add padding for better spacing */
          }
          .page-break {
            page-break-before: always; /* or page-break-after: always; */
          }

        `;

       await d.print(document.querySelector(".print-id"), [css]);

        setTimeout(() => {
            emit("input", false);
        }, 100);
};


</script>

<template>
    <div class="modal fade" :class="{ 'show': show_form_modal, 'd-block': show_form_modal }" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="ri-user-line me-2"></i>
                        Select Assignatoree
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="closeDialog(false)" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Prepared By: </label>
                            <vue-multiselect
                                v-model="form_assignatorees.prepared_by"
                                :options="users"
                                :multiple="false"
                                placeholder="Select Prepared By"
                                label="name"
                                track-by="id"
                                :allow-empty="false"
                                class="form-control p-0 border-0"
                                style="min-width: 200px;"
                            >
                            </vue-multiselect>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Noted By:</label>
                            <vue-multiselect
                                v-model="form_assignatorees.noted_by"
                                :options="assignatorees"
                                :multiple="false"
                                placeholder="Select Noted By"
                                label="name"
                                track-by="name"
                                :allow-empty="false"
                            >
                            </vue-multiselect>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDialog(false)">
                        <i class="ri-close-line me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-success" @click="printCSIReport()">
                        <i class="ri-printer-line me-1"></i>
                        Print Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div v-if="show_form_modal" class="modal-backdrop fade show"></div>


    <!-- Printouts-->
    <ByUnitMonthlyReport v-if="(form.csi_type == 'By Month' || form.csi_type == 'By Date') && is_printing && data" :form="form"  :data="data" :form_assignatorees="form_assignatorees" />
 
</template>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1055;
}

.modal-content {
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
  max-width: 1200px;
  width: 90%;
  max-height: 90%;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  background-color: #007bff;
  color: white;
  border-radius: 0.375rem 0.375rem 0 0;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: white;
  cursor: pointer;
}

.modal-body {
  padding: 1rem;
}

.preview-container {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 0.25rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #dee2e6;
  border-radius: 0 0 0.375rem 0.375rem;
}
</style>
