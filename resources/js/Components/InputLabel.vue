<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="custom-input-label">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<style scoped>
.custom-input-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}
</style>

