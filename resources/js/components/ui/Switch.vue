<script setup>
import { useId } from 'vue';
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import { cva } from 'cva';

const props = defineProps({
    required: { type: Boolean, default: false },
    /** The id attribute for the toggle */
    id: { type: String, default: () => useId() },
    /** The controlled value of the switch */
    modelValue: { type: Boolean, default: false },
    /** Controls the size of the switch. <br><br> Options: `xs`, `sm`, `base`, `lg` */
    size: { type: String, default: 'base' },
    /** Whether the switch is disabled */
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const switchRootClasses = cva({
    base: [
        'relative flex rounded-full shrink-0 border-2 with-contrast:border-1 with-contrast:border-gray-500!',
        'transition-colors',
        'data-[state=checked]:shadow-inner data-[state=checked]:border-switch-bg data-[state=checked]:bg-switch-bg',
        'data-[state=unchecked]:border-transparent',
        'data-[state=unchecked]:bg-gray-200 dark:data-[state=unchecked]:bg-gray-700',
        'data-[disabled]:opacity-50 [&:not([data-disabled])]:cursor-pointer data-[disabled]:cursor-not-allowed',
    ],
    variants: {
        size: {
            xs: 'h-3.5 w-6',
            sm: 'h-5 w-9',
            base: 'h-6 w-11',
            lg: 'h-7 w-14',
        },
    },
})({ size: props.size });

const switchThumbClasses = cva({
    base: [
        'my-auto flex items-center justify-center rounded-full bg-white text-xs shadow-ui-xl transition-transform will-change-transform',
        'data-[state=checked]:translate-x-full',
    ],
    variants: {
        size: {
            xs: 'size-2.5',
            sm: 'size-4',
            base: 'size-5',
            lg: 'size-6',
        },
    },
})({ size: props.size });
</script>

<template>
    <SwitchRoot
        data-ui-control
        dir="ltr"
        :id="id"
        :model-value="modelValue"
        :class="switchRootClasses"
        :disabled="disabled"
        @update:model-value="$emit('update:modelValue', $event)"
        data-ui-switch
    >
        <SwitchThumb :class="switchThumbClasses" />
    </SwitchRoot>
</template>
