<script setup>
import SectionTitle from './SectionTitle.vue';
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="action-section-content">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.action-section-content {
    background: #ffffff;
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
</style>

