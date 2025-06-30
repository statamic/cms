<script setup>
import { TabsRoot } from 'reka-ui';

const emit = defineEmits(['update:modelValue']);

defineProps({
    modelValue: { type: String, default: null },
});
</script>

<template>
    <TabsRoot :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
        <slot />
    </TabsRoot>
</template>
