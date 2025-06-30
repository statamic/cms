<script setup>
import { useSlots } from 'vue';
import { cva } from 'cva';
import { Icon, Button } from '@statamic/ui';

defineProps({
    icon: { type: String, default: null },
    appendIcon: { type: String, default: null },
    appendHref: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const usingSlot = !!slots.default;

const headerClasses = cva({
    base: 'col-span-2 px-3.5 py-3 bg-white dark:bg-gray-900 font-medium border-b border-gray-200 dark:border-black text-sm text-gray-800 dark:text-gray-300',
    variants: {
        usingSlot: {
            true: 'grid grid-cols-[auto_1fr_auto]',
        },
    },
});
</script>

<template>
    <header :class="headerClasses({ usingSlot: usingSlot })" data-ui-dropdown-header>
        <div
            v-if="icon"
            class="-ms-1 me-2 flex size-6 items-center justify-center rounded-lg bg-gray-100 p-1 text-gray-700 dark:bg-gray-800 dark:text-gray-400"
        >
            <Icon :name="icon" />
        </div>
        <div class="col-start-2 grow truncate">
            <slot v-if="usingSlot" />
            <template v-else>{{ text }}</template>
        </div>
        <Button
            v-if="appendIcon"
            :href="appendHref"
            :icon="appendIcon"
            class="[&_svg]:text-gray-700 dark:[&_svg]:text-gray-400"
            size="xs"
        />
    </header>
</template>
