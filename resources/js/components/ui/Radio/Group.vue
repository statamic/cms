<script setup>
import { useId } from 'vue';
import { RadioGroupRoot } from 'reka-ui';
import { WithField } from '@statamic/ui';

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
    <WithField :label :description :required>
        <RadioGroupRoot
            :modelValue="modelValue"
            @update:modelValue="$emit('update:modelValue', $event)"
            :name="name"
            class="relative block w-full space-y-2"
            :class="{ 'flex flex-wrap space-y-0 gap-x-4 gap-y-2': inline }"
            data-ui-input
            data-ui-radio-group
        >
            <slot />
        </RadioGroupRoot>
    </WithField>
</template>
