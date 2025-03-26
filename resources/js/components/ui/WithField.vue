<template>
    <ui-field v-if="label || description" :class="fieldClasses" :variant>
        <ui-label v-if="label" :text="label" :required :for :badge />
        <ui-description v-if="description" :text="description" />
        <slot v-bind="slotAttrs" />
    </ui-field>
    <slot v-else v-bind="$attrs" />
</template>

<script setup>
import { useAttrs, computed } from 'vue';

defineOptions({ inheritAttrs: false });
const rawAttrs = useAttrs();

// Extract wrapper classes specifically meant for the ui-field component
const fieldClasses = computed(() => {
    return rawAttrs.wrapperClass || rawAttrs.fieldClass || '';
});

// Prepare attributes for the child component/slot, excluding what we've used
const slotAttrs = computed(() => {
    const { wrapperClass, fieldClass, ...rest } = rawAttrs;
    return rest;
});

const props = defineProps({
    badge: { type: String, default: null },
    description: { type: String, default: null },
    for: { type: String, default: null },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    variant: { type: String, default: 'block' },
});
</script>
