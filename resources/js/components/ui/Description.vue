<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    /** Optional id so the description can be referenced by aria-describedby */
    id: { type: String, default: null },
    /** The description text to display */
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <div
        :id="id"
        class="text-sm font-normal text-gray-600/90 dark:text-gray-400 st-text-trim-start [&_a]:underline [&_a:hover]:text-gray-700 dark:[&_a:hover]:text-gray-300 [&_code]:px-0.5 [&_code]:rounded-sm"
        data-ui-description
    >
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
