<script setup>
const props = defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <header class="grid grid-cols-[1fr_auto] items-center justify-between gap-4 py-8" data-ui-header>
        <h1 class="text-[25px] font-medium antialiased">
            <slot name="title">{{ title }}</slot>
        </h1>
        <div class="flex items-center gap-3">
            <slot name="actions">
                <slot></slot>
            </slot>
        </div>
    </header>
</template>
