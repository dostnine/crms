<template>
    <Multiselect
        class="form-control w-lg"
        v-model="selectedValues"
        :options="options"
        label="name"
        value-prop="id"
        track-by="name"
        :object="true"
        :searchable="true"
        :placeholder="placeholder || 'Select option'"
        ref="multiselect"
        @update:modelValue="emitSelectedValues"
        :style="'border-color: ' + (message ? '#f06548' : '#ced4da') + ' !important; '"
    />
</template>
<script>
import Multiselect from "@vueform/multiselect";
export default {
    components: { Multiselect },
    props: ['options','modelValue','message','placeholder'],
    data() {
        return {
            selectedValues: this.modelValue
        };
    },
    watch: {
        modelValue(value) {
            this.selectedValues = value;
        },
    },
    methods: {
        emitSelectedValues(values) {
            this.selectedValues = values;
            this.$emit('update:modelValue', values); 
        },
        clear(){
            this.$refs.multiselect.clear();
        },
    }
}
</script>
<style scoped>
/* Pretty dashboard styling - All Services Units exact match */
.multiselect {
  --ms-bg: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  --ms-border: 1px solid #e2e8f0;
  --ms-border-focus: 2px solid #3b82f6;
  --ms-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --ms-shadow-focus: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --ms-dropdown-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --ms-radius: 8px;
  --ms-text: #374151;
  --ms-text-muted: #9ca3af;
  --ms-hover: #f3f4f6;
  --ms-selected: #2563eb;
}

/* Main container - clean, modern input look */
:deep(.multiselect) {
  --ms-option-bg-selected: #e8f2ff;
  --ms-option-color-selected: #153b70;
  --ms-option-bg-selected-pointed: #dcecff;
  --ms-option-color-selected-pointed: #153b70;
  --ms-option-bg-pointed: #f3f8ff;
  --ms-option-color-pointed: #153b70;
  border: var(--ms-border);
  border-radius: var(--ms-radius);
  background: var(--ms-bg);
  box-shadow: var(--ms-shadow);
  transition: all 0.15s ease-in-out;
  font-size: 14px;
  line-height: 1.5;
  min-height: 42px;
  cursor: pointer;
}

/* Hover effect - subtle lift */
:deep(.multiselect:hover) {
  border-color: #cbd5e1;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Focus - blue ring, elevated */
:deep(.multiselect.focused),
:deep(.multiselect:focus-within) {
  border-color: #7fb7ea;
  box-shadow: 0 0 0 3px rgba(34, 102, 168, 0.12);
  outline: none;
}

/* Single value display */
:deep(.multiselect-single-label) {
  color: var(--ms-text);
  font-weight: 500;
  padding-left: 12px;
}

/* Placeholder - muted, italic */
:deep(.multiselect-placeholder) {
  color: var(--ms-text-muted);
  font-style: italic;
  opacity: 0.8;
  padding-left: 12px;
}

/* Multi-select tags container */
:deep(.multiselect-tags) {
  min-height: 42px;
  padding: 8px 12px;
}

/* Search input */
:deep(.multiselect-search) {
  font-size: 14px;
  min-height: 32px;
}

/* Dropdown - elevated panel */
:deep(.multiselect-dropdown) {
  z-index: 99999 !important;
  border-radius: var(--ms-radius);
  border: var(--ms-border);
  background: white;
  box-shadow: var(--ms-dropdown-shadow);
  margin-top: 4px;
  max-height: 280px;
  overflow: auto;
  animation: dropdownAppear 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Dropdown animation */
@keyframes dropdownAppear {
  from {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Option items - smooth hover */
:deep(.multiselect-option) {
  padding: 12px 16px;
  transition: all 0.15s ease;
  border-bottom: 1px solid #f9fafb;
  cursor: pointer;
  position: relative;
  background: #ffffff;
  color: #12243a;
}

:deep(.multiselect-option:hover),
:deep(.multiselect-option.is-pointed) {
  background: #f3f8ff;
  color: #153b70;
}

:deep(.multiselect-option.is-selected) {
  background: #e8f2ff;
  color: #153b70;
  font-weight: 500;
}

:deep(.multiselect-option.is-selected.is-pointed) {
  background: #dcecff;
  color: #153b70;
}

/* Selected option checkmark */
:deep(.multiselect-option.is-selected::after) {
  content: 'Selected';
  position: absolute;
  right: 16px;
  color: var(--ms-selected);
  font-weight: bold;
}

/* Multi-select tags - pretty chips */
:deep(.multiselect-tag) {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border: 1px solid rgba(37, 99, 235, 0.2);
  border-radius: 20px;
  color: var(--ms-selected);
  font-size: 13px;
  font-weight: 500;
  padding: 4px 12px;
  margin-right: 6px;
  margin-bottom: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Tag remove button */
:deep(.multiselect-tag-remove) {
  color: var(--ms-selected);
  margin-left: 8px;
  font-size: 16px;
  line-height: 1;
}

:deep(.multiselect-tag-remove:hover) {
  background: none;
  color: var(--ms-selected);
  transform: scale(1.1);
}

/* Clear button */
:deep(.multiselect-clear) {
  color: #9ca3af;
}

:deep(.multiselect-clear:hover) {
  color: #6b7280;
}

/* Spinner */
:deep(.multiselect-spinner) {
  border-color: var(--ms-selected);
}

/* No results */
:deep(.multiselect-no-results) {
  color: #9ca3af;
  font-style: italic;
  padding: 16px;
}

/* Responsive */
@media (max-width: 576px) {
  :deep(.multiselect-dropdown) {
    left: 0 !important;
    right: 0 !important;
    border-radius: 8px 8px 0 0;
    width: 100vw;
    max-width: none;
  }
}
</style>
