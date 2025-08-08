<script setup>
import { TabsRoot } from 'reka-ui';

const emit = defineEmits(['update:modelValue']);

defineProps({
    modelValue: { type: String, default: null },
    unmountOnHide: { type: Boolean, default: true },
});
</script>

<template>
    <TabsRoot :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)" :unmount-on-hide>
        <slot />
    </TabsRoot>
</template>
