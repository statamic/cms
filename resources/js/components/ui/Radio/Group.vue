<script setup>
import { computed, provide, useId } from 'vue';
import { RadioGroupRoot } from 'reka-ui';

const props = defineProps({
    /** Controls how the radio group is displayed. Options: `default`, `inline`, `chips` */
    appearance: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'inline', 'chips'].includes(value),
    },
    /** @deprecated Use `appearance="inline"` instead. */
    inline: { type: Boolean, default: false },
    /** The controlled value of the radio group */
    modelValue: { type: String, default: null },
    /** Name attribute for the radio group */
    name: { type: String, default: () => useId() },
    required: { type: Boolean, default: false },
});

const resolvedAppearance = computed(() => {
    if (props.appearance !== 'default') {
        return props.appearance;
    }

    return props.inline ? 'inline' : 'default';
});

provide('radioAppearance', resolvedAppearance);

const focus = function () {
    console.log('focusing. todo.');
};

defineEmits(['update:modelValue']);

defineExpose({ focus });
</script>

<template>
    <RadioGroupRoot
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        :name="name"
        class="relative block w-full space-y-2"
        :class="{
            'flex flex-wrap space-y-0 gap-x-4 gap-y-2': resolvedAppearance === 'inline' || resolvedAppearance === 'chips',
            'gap-x-2.5!': resolvedAppearance === 'chips',
        }"
        :data-appearance="resolvedAppearance !== 'default' ? resolvedAppearance : undefined"
        data-ui-input
        data-ui-radio-group
    >
        <slot />
    </RadioGroupRoot>
</template>
