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
        class="translate-y-px cursor-pointer px-2 py-1 focus-visible:rounded-lg hover:text-gray-600 border-b border-gray-200 dark:border-gray-800 data-[state=active]:border-gray-800 data-[state=active]:text-gray-900 dark:hover:text-gray-400 dark:data-[state=active]:border-gray-200 dark:data-[state=active]:text-gray-200"
    >
        <slot v-if="hasSlot" />
        <span v-else>{{ text }}</span>
    </TabsTrigger>
</template>
