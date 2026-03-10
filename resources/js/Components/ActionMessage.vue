<script setup>
defineProps({
    on: Boolean,
});
</script>

<template>
    <div>
        <transition leave-active-class="transition ease-in duration-1000" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="on" class="action-message">
                <slot />
            </div>
        </transition>
    </div>
</template>

<style scoped>
.action-message {
    font-size: 0.875rem;
    color: #16a34a;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.action-message::before {
    content: '✓';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #dcfce7;
    color: #16a34a;
    border-radius: 50%;
    margin-right: 8px;
    font-size: 0.75rem;
}
</style>

