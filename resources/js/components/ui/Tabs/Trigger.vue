<script setup>
import { TabsTrigger } from 'reka-ui'
import { useSlots } from 'vue'

const slots = useSlots();
const hasSlot = !!slots.default;

defineProps({
    text: { type: String, default: null },
    name: { type: String, default: null },
});
</script>

<template>
<TabsTrigger :value="name" class="pb-1 hover:text-gray-600 dark:hover:text-gray-400 cursor-pointer data-[state=active]:text-gray-800 dark:data-[state=active]:text-gray-200">
    <slot v-if="hasSlot" />
    <span v-else>{{ text }}</span>
</TabsTrigger>
</template>
