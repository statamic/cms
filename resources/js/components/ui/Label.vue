<script setup>
import { useSlots } from 'vue';
import Badge from './Badge.vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    /** The ID of the form element this label is for */
    for: { type: String, default: null },
    /** Optional badge text to display on the right side of the label */
    badge: { type: String, default: '' },
    required: { type: Boolean, default: false },
    /** The label text to display */
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <label
        class="flex justify-between mb-1.5 text-sm font-medium [&_button]:font-medium text-gray-925 select-none dark:text-gray-300"
        data-ui-label
        :for="for"
    >
        <div>
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
            <span v-if="required" class="relative -top-px ms-0.5 text-red-600">*</span>
        </div>
        <Badge v-if="badge" :text="badge" />
    </label>
</template>
