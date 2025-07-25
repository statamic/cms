<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <div class="text-sm font-normal text-red-500 trim-text-start [[data-ui-panel-header]_&]:text-red-600 [&_a]:underline [&_a:hover]:text-red-700 dark:[&_a:hover]:text-red-300" data-ui-error-message>
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
