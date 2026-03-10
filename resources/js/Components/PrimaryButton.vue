<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="primary-button">
        <slot />
    </button>
</template>

<style scoped>
.primary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #1f6db3 0%, #1a4f89 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.primary-button:hover {
    background: linear-gradient(135deg, #1a5d99 0%, #153f6d 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 79, 137, 0.3);
}

.primary-button:active {
    transform: translateY(0);
}

.primary-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.primary-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>

