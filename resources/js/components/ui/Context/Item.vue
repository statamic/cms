<script setup>
import { ContextMenuItem } from 'reka-ui';
import { useSlots } from 'vue';
import { Icon } from '@statamic/ui';

defineProps({
    href: { type: String, default: null },
    icon: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
</script>

<template>
    <ContextMenuItem
        :class="[
            'col-span-2 grid grid-cols-subgrid items-center',
            'rounded-lg px-1 py-1.5 text-sm antialiased',
            'text-gray-700 dark:text-gray-300',
            'not-data-disabled:cursor-pointer data-disabled:opacity-50',
            'hover:not-data-disabled:bg-gray-50 dark:hover:not-data-disabled:bg-gray-950',
            'outline-hidden focus-visible:bg-gray-100 dark:focus-visible:bg-gray-950',
        ]"
        data-ui-context-item
        :as="href ? 'a' : 'div'"
        :href="href"
    >
        <div v-if="icon" class="flex size-6 items-center justify-center p-1 text-gray-500">
            <Icon :name="icon" class="size-3.5!" />
        </div>
        <div class="col-start-2 ps-2">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
    </ContextMenuItem>
</template>
