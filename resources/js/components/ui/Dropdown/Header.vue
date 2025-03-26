<script setup>
import { useSlots } from 'vue';

defineProps({
    icon: { type: String, default: null },
    linkToConfig: { type: Boolean, default: false },
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
</script>

<template>
    <header class="col-span-2 grid grid-cols-[auto_1fr_auto] items-center px-3.5 py-3 bg-white dark:bg-gray-900 font-medium border-b border-gray-200 dark:border-black text-sm text-gray-800 dark:text-gray-300" data-ui-dropdown-header>
        <div v-if="icon" class="size-6 -ms-1 me-2 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 p-1 text-gray-700 dark:text-gray-400">
            <ui-icon :name="icon" />
        </div>
        <div class="flex-grow truncate col-start-2">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
        <ui-button v-if="linkToConfig" icon="cog-solid" size="xs" class="[&_svg]:text-gray-700 dark:[&_svg]:text-gray-400" />
    </header>
</template>
