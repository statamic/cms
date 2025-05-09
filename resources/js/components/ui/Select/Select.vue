<script setup>
import { cva } from 'cva';
import {
    SelectContent,
    SelectItem,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';
import { useAttrs } from 'vue';
import { WithField, Icon } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    description: { type: String, default: null },
    label: { type: String, default: null },
    modelValue: { type: [Object, String, Number], default: null },
    size: { type: String, default: 'base' },
    placeholder: { type: String, default: 'Select...' },
    options: { type: Array, default: null },
    flat: { type: Boolean, default: false },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const triggerClasses = cva({
    base: 'w-full flex items-center justify-between border border-gray-300 dark:border-b-0 dark:ring-3 dark:ring-gray-900 dark:border-white/15 text-gray-600 dark:text-gray-300 antialiased appearance-none shadow-ui-sm dark:shadow-md not-prose',
    variants: {
        size: {
            base: 'text-base rounded-lg ps-3 py-2 h-10 leading-[1.375rem]',
            sm: 'text-sm rounded-md ps-2.5 py-1.5 h-8 leading-[1.125rem]',
            xs: 'text-xs rounded-xs ps-2 py-1.5 h-6 leading-[1.125rem]',
        },
        flat: {
            true: 'shadow-none',
            false: 'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 dark:from-gray-800/30 dark:to-gray-800 dark:hover:to-gray-850 shadow-ui-sm',
        },
    },
})({ ...props });

const itemClasses = cva({
    base: 'antialiased rounded-lg py-1.5 px-2 flex items-center gap-2 text-gray-600 dark:text-gray-300 relative select-none data-disabled:text-gray-300 data-disabled:pointer-events-none data-highlighted:outline-hidden data-highlighted:bg-gray-50 data-highlighted:text-gray-900 dark:data-highlighted:bg-gray-700 dark:data-highlighted:text-gray-300',
    variants: {
        size: {
            base: '',
            sm: 'text-sm',
            xs: 'text-xs',
        },
    },
})({ size: props.size });
</script>

<template>
    <WithField :label :description>
        <SelectRoot v-bind="attrs" :model-value="modelValue" @update:model-value="emit('update:model-value', $event)">
            <SelectTrigger :class="[triggerClasses, $attrs.class]" data-ui-select-trigger>
                <SelectValue :placeholder="placeholder" class="select-none" />
                <Icon name="ui/chevron-down" class="me-2" />
            </SelectTrigger>

            <SelectPortal>
                <SelectContent
                    position="popper"
                    :side-offset="5"
                    :class="[
                        'shadow-ui-sm z-100 rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-gray-800',
                        'max-h-[var(--reka-select-content-available-height)] w-[var(--reka-select-trigger-width)]',
                    ]"
                >
                    <SelectViewport>
                        <SelectItem
                            v-if="options"
                            v-for="(option, index) in options"
                            :key="index"
                            :value="option.value"
                            :text-value="option.label"
                            :class="itemClasses"
                        >
                            <slot name="option" v-bind="option">
                                <img v-if="option.image" :src="option.image" class="size-5 rounded-full" />
                                <SelectItemText v-html="option.label" />
                            </slot>
                        </SelectItem>
                    </SelectViewport>
                </SelectContent>
            </SelectPortal>
        </SelectRoot>
    </WithField>
</template>
