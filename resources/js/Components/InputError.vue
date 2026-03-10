<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="input-error">
        <p class="error-text">
            {{ message }}
        </p>
    </div>
</template>

<style scoped>
.input-error {
    margin-top: 6px;
}

.error-text {
    font-size: 0.8rem;
    color: #dc2626;
    font-weight: 500;
}
</style>

