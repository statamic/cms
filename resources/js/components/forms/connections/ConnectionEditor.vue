<script setup>
defineEmits(['update:modelValue']);

defineProps({
    form: Object,
    component: Object,
    modelValue: [Array, Object],
    errors: { type: Object, default: () => ({}) },
});
</script>

<template>
    <component
        :is="component.name"
        :form
        :errors
        :model-value
        v-bind="component.props"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>
