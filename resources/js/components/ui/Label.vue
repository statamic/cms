<script setup>
import { useSlots } from 'vue';
import { Badge } from '@statamic/ui';

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const props = defineProps({
    for: { type: String, default: null },
    badge: { type: String, default: '' },
    required: { type: Boolean, default: false },
    text: { type: [String, Number, Boolean, null], default: null },
});
</script>

<template>
    <label
        class="flex justify-between text-sm font-medium text-gray-800 antialiased select-none dark:text-gray-300"
        data-ui-label
        :for="for"
    >
        <div>
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
            <span v-if="required" class="relative -top-px ms-0.5 text-red-600">*</span>
        </div>
        <Badge v-if="badge" :text="badge" variant="flat" />
    </label>
</template>
