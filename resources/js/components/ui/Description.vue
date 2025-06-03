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
    <div class="text-[0.8125rem] font-normal text-gray-500 dark:text-gray-400 trim-text-start" data-ui-description>
        <slot v-if="hasDefaultSlot" />
        <span v-else v-html="text" />
    </div>
</template>
