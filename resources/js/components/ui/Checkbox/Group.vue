<script setup>
import { useId } from 'vue';
import { CheckboxGroupRoot } from 'reka-ui';

defineProps({
    description: { type: String, default: null },
    inline: { type: Boolean, default: false },
    label: { type: String, default: null },
    modelValue: { type: Array, default: () => [] },
    name: { type: String, default: () => useId() },
    required: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <ui-with-field :label :description :required>
        <CheckboxGroupRoot
            :modelValue="modelValue"
            @update:modelValue="$emit('update:modelValue', $event)"
            :name="name"
            class="w-full relative block space-y-2"
            :class="{ 'flex flex-wrap gap-x-4 gap-y-2 space-y-0': inline }"
            data-ui-input
        >
            <slot />
        </CheckboxGroupRoot>
    </ui-with-field>
</template>
