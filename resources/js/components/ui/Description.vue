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
    <div class="text-sm font-normal text-gray-500 dark:text-gray-400 st-text-trim-start [[data-ui-panel-header]_&]:text-gray-600 [&_a]:underline [&_a:hover]:text-gray-700 dark:[&_a:hover]:text-gray-300" data-ui-description>
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
