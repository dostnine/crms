<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="secondary-button">
        <slot />
    </button>
</template>

<style scoped>
.secondary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.secondary-button:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-color: #94a3b8;
    color: #1e293b;
}

.secondary-button:active {
    transform: translateY(0);
}

.secondary-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.3);
}

.secondary-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

