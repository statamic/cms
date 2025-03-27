<script setup>
import { useId } from 'vue';
import { RadioGroupRoot } from 'reka-ui';

defineProps({
    description: { type: String, default: null },
    inline: { type: Boolean, default: false },
    label: { type: String, default: null },
    modelValue: { type: [String, Number, Boolean], default: null },
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
    <ui-with-field :label :description :required>
        <RadioGroupRoot
            :modelValue="modelValue"
            @update:modelValue="$emit('update:modelValue', $event)"
            :name="name"
            class="w-full relative block space-y-2"
            :class="{ 'flex flex-wrap gap-x-4 gap-y-2 space-y-0': inline }"
            data-ui-input
        >
            <slot />
        </RadioGroupRoot>
    </ui-with-field>
</template>
