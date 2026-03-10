<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="danger-button">
        <slot />
    </button>
</template>

<style scoped>
.danger-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.danger-button:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.danger-button:active {
    transform: translateY(0);
}

.danger-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.3);
}

.danger-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>

