<script setup>
import { TabsTrigger } from 'reka-ui';
import { useSlots } from 'vue';

const slots = useSlots();
const hasSlot = !!slots.default;

defineProps({
    /** Display text for the tab */
    text: { type: String, default: null },
	/** Unique tab name. Should match the name of a `TabContent` component. */
    name: { type: String, default: null },
});
</script>

<template>
    <TabsTrigger
        :value="name"
        class="cursor-pointer p-2 pb-1 hover:text-gray-600 data-[state=active]:text-gray-900 dark:hover:text-gray-400 dark:data-[state=active]:text-gray-200"
    >
        <slot v-if="hasSlot" />
        <span v-else>{{ text }}</span>
    </TabsTrigger>
</template>
