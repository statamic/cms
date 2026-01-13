<script setup>
import { TabTrigger } from '@/components/ui';
import { useSlots } from 'vue';

const slots = useSlots();
const hasSlot = !!slots.default;

defineProps({
    text: { type: String, default: null },
    name: { type: String, default: null },
});
</script>

<template>
    <TabTrigger :name="name">
        <slot v-if="hasSlot" />
        <span v-else>{{ text }}</span>
    </TabTrigger>
</template>
