<script setup>
import { useId } from 'vue';
import { CheckboxGroupRoot } from 'reka-ui';

defineProps({
    inline: { type: Boolean, default: false },
    modelValue: { type: Array, default: () => [] },
    name: { type: String, default: () => useId() },
    required: { type: Boolean, default: false },
});

const focus = function () {
    console.log('focusing. todo.');
};

defineEmits(['update:modelValue']);

defineExpose({ focus });
</script>

<template>
    <CheckboxGroupRoot
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        :name="name"
        class="relative block w-full space-y-2"
        :class="{ 'flex flex-wrap space-y-0 gap-x-4 gap-y-2': inline }"
        data-ui-input
    >
        <slot />
    </CheckboxGroupRoot>
</template>
