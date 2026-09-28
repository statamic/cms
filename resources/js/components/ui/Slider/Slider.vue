<script setup>
import { useId } from 'vue';
import { cva } from 'cva';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui';

const props = defineProps({
    /** Description text for the slider. */
    description: { type: String, default: null },
    /** ID attribute for the slider. */
    id: { type: String, default: () => useId() },
    /** Label text for the slider. */
    label: { type: String, default: null },
    /** The controlled value of the slider. */
    modelValue: { type: Number, default: 0 },
    /** The minimum value of the slider. */
    min: { type: Number, default: 0 },
    /** The maximum value of the slider. */
    max: { type: Number, default: 100 },
    /** The step increment for the slider. */
    step: { type: Number, default: 1 },
    /** Controls the size of the slider. <br><br> Options: `sm`, `base` */
    size: { type: String, default: 'base' },
    /** Controls the appearance of the slider. <br><br> Options: `default` */
    variant: { type: String, default: 'default' },
});

defineEmits(['update:modelValue']);

const rootClasses = cva({
    base: 'relative flex w-full touch-none items-center select-none',
    variants: {
        size: {
            sm: 'h-2',
            base: 'h-5',
        },
    },
})({ ...props });

const trackClasses = cva({
    base: 'relative grow rounded-full bg-gray-300/80 dark:bg-gray-800',
    variants: {
        size: {
            sm: 'h-1',
            base: 'h-2',
        },
    },
})({ ...props });

const rangeClasses = cva({
    base: 'absolute h-full rounded-full',
    variants: {
        variant: {
            default: 'bg-gray-900',
        },
    },
})({ ...props });

const thumbClasses = cva({
    base: 'shadow-ui-md block rounded-full bg-white dark:bg-gray-400 focus:outline-hidden',
    variants: {
        size: {
            sm: 'size-4',
            base: 'size-5',
        },
        variant: {
            default: 'border-2 border-gray-900 hover:bg-gray-50',
        },
    },
})({ ...props });
</script>

<template>
    <SliderRoot
        data-ui-control
        :class="rootClasses"
        :id
        :max="max"
        :min="min"
        :step="step"
        :model-value="[modelValue]"
        @update:model-value="$emit('update:modelValue', $event[0])"
    >
        <SliderTrack :class="trackClasses">
            <SliderRange :class="rangeClasses" />
        </SliderTrack>
        <SliderThumb
            :class="thumbClasses"
            :aria-label="label"
        />
    </SliderRoot>
</template>
