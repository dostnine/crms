<template>
  <div class="vs__dropdown-wrapper" ref="wrapper">
    <div class="vs__selected-options">
      <div v-if="!multiple && selectedOption" class="vs__selected">
        {{ getOptionLabel(selectedOption) }}
      </div>
      <div v-else-if="!multiple" class="vs__placeholder">
        {{ placeholder }}
      </div>
      <input 
        ref="search"
        class="vs__search"
        :class="{ 'vs__search--open': isOpen }"
        v-model="search"
        :placeholder="placeholder"
        @input="onSearch"
        @focus="openDropdown"
        @keydown="onKeydown"
        autocomplete="off"
      />
    </div>
    <button type="button" class="vs__clear" v-if="selectedOption && clearable" @click.stop="clearSelected">
      <svg viewBox="0 0 24 24">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
      </svg>
    </button>
    <button type="button" class="vs__dropdown-toggle" @click.stop="toggleDropdown">
      <svg viewBox="0 0 24 24">
        <path d="M7 10l5 5 5 -5z"/>
      </svg>
    </button>
    <ul v-if="isOpen" class="vs__dropdown-menu" ref="dropdown-menu">
      <li v-if="filteredOptions.length === 0" class="vs__no-options">
        No options found
      </li>
      <li 
        v-for="(option, index) in filteredOptions"
        :key="getOptionKey(option)"
        class="vs__dropdown-option"
        :class="{ 'selected': isSelected(option), 'highlighted': hoverIndex === index }"
        @mouseenter="hoverIndex = index"
        @mousedown.prevent="selectOption(option)"
      >
        {{ getOptionLabel(option) }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: null,
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: 'label'
  },
  valueProp: {
    type: String,
    default: 'value'
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  closeOnSelect: {
    type: Boolean,
    default: true
  },
  clearable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue'])

const wrapper = ref(null)
const search = ref('')
const isOpen = ref(false)
const hoverIndex = ref(-1)

const selectedOption = computed(() => {
  return props.options.find(opt => opt[props.valueProp] === props.modelValue)
})

const filteredOptions = computed(() => {
  let opts = props.options
  if (search.value) {
    opts = opts.filter(opt => 
      opt[props.label].toLowerCase().includes(search.value.toLowerCase())
    )
  }
  return opts
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => {
      wrapper.value?.focus()
    })
  }
}

const closeDropdown = () => {
  isOpen.value = false
  search.value = ''
  hoverIndex.value = -1
}

const openDropdown = () => {
  isOpen.value = true
}

const selectOption = (option) => {
  emit('update:modelValue', option[props.valueProp])
  if (props.closeOnSelect) {
    closeDropdown()
  }
}

const clearSelected = () => {
  emit('update:modelValue', null)
}

const getOptionLabel = (option) => {
  return option[props.label] || option
}

const getOptionKey = (option) => {
  return option[props.valueProp] || option
}

const isSelected = (option) => {
  return props.modelValue === option[props.valueProp]
}

const onSearch = () => {
  if (!isOpen.value) {
    isOpen.value = true
  }
}

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!wrapper.value?.contains(e.target)) {
      closeDropdown()
    }
  })
})
</script>

<style scoped>
.vs__dropdown-wrapper {
  position: relative;
  width: 100%;
}

.vs__selected-options {
  display: flex;
  align-items: center;
}

.vs__search {
  flex: 1;
  background: transparent;
  border: none;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  color: #1f3956;
  min-height: 40px;
}

.vs__search:focus {
  outline: none;
}

.vs__clear, .vs__dropdown-toggle {
  background: none;
  border: none;
  padding: 0.75rem;
  cursor: pointer;
  color: #64748b;
}

.vs__dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  z-index: 1060;
  max-height: 250px;
  overflow-y: auto;
  list-style: none;
  margin: 0;
  padding: 0;
}

.vs__dropdown-option {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.vs__dropdown-option:hover, .vs__dropdown-option.highlighted {
  background: #f8fafc;
}

.vs__dropdown-option.selected {
  background: #dbeafe;
  font-weight: 500;
}

.vs__no-options {
  padding: 1rem;
  text-align: center;
  color: #94a3b8;
}

.vs__search--open ~ .vs__dropdown-menu {
  display: block;
}
</style> 

