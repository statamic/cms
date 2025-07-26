<script setup>
import { Icon } from '@statamic/ui';

const props = defineProps({
    icon: { type: String, default: null },
    title: { type: String, default: null },
});
</script>

<template>
    <header class="grid grid-cols-[1fr_auto] items-end justify-between gap-4 py-8" data-ui-header>
        <h1 class="text-[25px] leading-[1.25] st-text-legibility font-medium antialiased flex items-center gap-2.5">
            <Icon v-if="icon" :name="icon" class="size-5 text-gray-500"></Icon>
            <slot name="title">{{ title }}</slot>
        </h1>
        <div class="flex items-center gap-3">
            <slot name="actions">
                <slot></slot>
            </slot>
        </div>
    </header>
</template>
