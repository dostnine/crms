<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="custom-text-input"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

<style scoped>
.custom-text-input {
    width: 100%;
    padding: 10px 14px;
    font-size: 0.95rem;
    color: #334155;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    transition: all 0.2s ease;
    outline: none;
}

.custom-text-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.custom-text-input:hover:not(:focus) {
    border-color: #94a3b8;
}

.custom-text-input::placeholder {
    color: #94a3b8;
}
</style>

