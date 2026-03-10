<template>
    <div class="md:col-span-1 flex justify-between">
        <div class="px-4 sm:px-0">
            <h3 class="section-title">
                <slot name="title" />
            </h3>

            <p class="section-description">
                <slot name="description" />
            </p>
        </div>

        <div class="px-4 sm:px-0">
            <slot name="aside" />
        </div>
    </div>
</template>

<style scoped>
.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #153b70;
    margin-bottom: 4px;
}

.section-description {
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}
</style>

