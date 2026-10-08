<script setup>
import { computed } from 'vue';
import { TabsRoot } from 'reka-ui';
import { useUiDirection } from '@/composables/ui-direction';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    /** The controlled value of the tabs */
    modelValue: { type: String, default: null },
    /** When `true`, the element will be unmounted on closed state */
    unmountOnHide: { type: Boolean, default: true },
    /** The reading direction of the tabs */
    dir: { type: String, default: null },
});

const { direction } = useUiDirection();

const dir = computed(() => props.dir ?? direction.value);
</script>

<template>
    <TabsRoot :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)" :unmount-on-hide :dir>
        <slot />
    </TabsRoot>
</template>
