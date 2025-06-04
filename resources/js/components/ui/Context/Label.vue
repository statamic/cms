<script setup>
import { ContextMenuLabel } from 'reka-ui';
import { useSlots } from 'vue';

defineProps({
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
</script>

<template>
    <ContextMenuLabel
        class="col-span-2 grid items-center rounded-lg px-2 py-2 text-xs text-gray-500 dark:text-gray-400"
        data-ui-context-label
    >
        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>
    </ContextMenuLabel>
</template>
