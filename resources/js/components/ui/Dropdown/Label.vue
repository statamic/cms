<script setup>
import { DropdownMenuLabel } from 'reka-ui';
import { useSlots } from 'vue';

defineProps({
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
</script>

<template>
    <DropdownMenuLabel
        class="col-span-2 grid items-center rounded-lg px-1 py-2 text-xs text-gray-500 antialiased dark:text-gray-400"
        data-ui-dropdown-label
    >
        <slot v-if="hasDefaultSlot" />
        <template v-else>{{ text }}</template>
    </DropdownMenuLabel>
</template>
