<script setup>
import { TabsRoot } from 'reka-ui';

const emit = defineEmits(['update:modelValue']);

defineProps({
    /** The controlled value of the tabs */
    modelValue: { type: String, default: null },
    /** When `true`, the element will be unmounted on closed state */
    unmountOnHide: { type: Boolean, default: true },
});
</script>

<template>
    <TabsRoot :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)" :unmount-on-hide>
        <slot />
    </TabsRoot>
</template>
