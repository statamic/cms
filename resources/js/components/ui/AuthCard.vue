<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: { type: String, default: null },
    title: { type: String, default: null },
    description: { type: String, default: null },
});

const hasHeader = computed(() => {
    return props.icon || props.title || props.description;
});
</script>

<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)] backdrop-blur-[2px]"
    >
        <div
            class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]"
        >
            <header class="mb-8 flex flex-col items-center justify-center py-3" v-if="hasHeader">
                <ui-card class="mb-4 flex items-center justify-center p-2!" v-if="icon">
                    <ui-icon :name="icon" class="size-5" />
                </ui-card>
                <ui-heading :level="1" size="xl" v-if="title" v-text="title" />
                <ui-description :text="description" v-if="description" class="mt-1.5 text-center" />
            </header>
            <slot />
        </div>
    </div>
</template>
