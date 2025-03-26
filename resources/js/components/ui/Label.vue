<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    badge: { type: String, default: '' },
    required: { type: Boolean, default: false },
    text: { type: String, default: null }
});
</script>

<template>
    <label class="flex justify-between text-sm font-medium text-gray-800 dark:text-gray-300 antialiased select-none" data-ui-label>
        <div>
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
            <span v-if="required" class="text-red-600 ms-0.5 relative -top-px">*</span>
        </div>
        <ui-badge v-if="badge" :text="badge" variant="flat" />
    </label>
</template>
