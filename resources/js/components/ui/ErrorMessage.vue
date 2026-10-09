<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    /** Optional id so the error can be referenced by aria-describedby */
    id: { type: String, default: null },
    /** The error message to display */
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <div
        :id="id"
        class="text-sm font-normal text-red-600 st-text-trim-start text-pretty @5xl/panel:text-balance [[data-ui-panel-header]_&]:text-red-600 [&_a]:underline [&_a:hover]:text-red-700 dark:[&_a:hover]:text-red-300"
        data-ui-error-message
    >
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
