<script setup>
import Icon from './Icon/Icon.vue';

const props = defineProps({
    icon: { type: String, default: null },
    title: { type: String, default: null },
});
</script>

<template>
    <header class="flex flex-wrap items-center justify-between gap-4 px-2 sm:px-0 py-6 max-md:pb-8 md:py-8" data-ui-header>
        <h1 class="text-[25px] leading-[1.25] st-text-legibility font-medium antialiased flex items-center gap-2.5 md:flex-1">
            <!-- Wrap icon in a fixed size div (the same size as the icon) to prevent layout shift once it loads -->
            <div v-if="icon" class="size-5 relative">
                <Icon :name="icon" class="size-5 text-gray-500"></Icon>
            </div>
            <slot name="title">{{ title }}</slot>
        </h1>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <slot name="actions">
                <slot></slot>
            </slot>
        </div>
    </header>
</template>
