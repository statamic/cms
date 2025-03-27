<script setup>
import { useSlots } from 'vue';
import { cva } from 'cva';

defineProps({
    icon: { type: String, default: null },
    linkToConfig: { type: Boolean, default: false },
    appendIcon: { type: String, default: null },
    appendHref: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const headerClasses = cva({
    base: 'col-span-2 px-3.5 py-3 bg-white dark:bg-gray-900 font-medium border-b border-gray-200 dark:border-black text-sm text-gray-800 dark:text-gray-300',
    variants: {
        usingSlot: {
            true: 'grid grid-cols-[auto_1fr_auto]',
            false: '',
        },
    },
});
</script>

<template>
    <header :class="headerClasses({ usingSlot: hasDefaultSlot })" data-ui-dropdown-header>
        <div v-if="icon" class="size-6 -ms-1 me-2 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 p-1 text-gray-700 dark:text-gray-400">
            <ui-icon :name="icon" />
        </div>
        <div class="grow truncate col-start-2">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
        <ui-button
            v-if="appendIcon"
            :href="appendHref"
            :icon="appendIcon"
            class="[&_svg]:text-gray-700 dark:[&_svg]:text-gray-400"
            size="xs"
        />
    </header>
</template>
