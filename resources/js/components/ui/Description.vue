<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    required: { type: Boolean, default: false },
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <div
        class="trim-text-start text-xs font-normal tracking-tight text-gray-500 dark:text-gray-400 [[data-ui-panel-header]_&]:text-gray-600"
        data-ui-description
    >
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
