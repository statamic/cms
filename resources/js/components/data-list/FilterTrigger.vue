<script setup>
import { useSlots } from 'vue';

const slots = useSlots();
const hasSlot = !!slots.default;

defineProps({
    text: { type: String, default: null },
    name: { type: String, default: null },
    active: { type: Boolean, default: false },
});
</script>

<template>
    <button
        :data-state="active ? 'active' : null"
        :value="name"
        class="
            cursor-pointer p-2 pb-1 hover:text-gray-600 dark:hover:text-gray-400 translate-y-px
            data-[state=active]:text-gray-800 dark:data-[state=active]:text-gray-200
            data-[state=active]:border-b data-[state=active]:border-gray-800 dark:data-[state=active]:border-gray-200
        "
    >
        <slot v-if="hasSlot" />
        <span v-else>{{ text }}</span>
    </button>
</template>
