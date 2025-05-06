<script setup>
import { cva } from 'cva';
import {
    ComboboxAnchor, ComboboxCancel,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxLabel,
    ComboboxRoot,
    ComboboxSeparator,
    ComboboxTrigger,
    ComboboxPortal,
    ComboboxViewport,
} from 'reka-ui';
import { computed, ref, useAttrs } from 'vue';
import { WithField, Icon } from '@statamic/ui';
import fuzzysort from 'fuzzysort';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    description: { type: String, default: null },
    label: { type: String, default: null },
    modelValue: { type: [Object, String], default: null },
    size: { type: String, default: 'base' },
    placeholder: { type: String, default: 'Select...' },
    multiple: { type: Boolean, default: false },
    clearable: { type: Boolean, default: false },
    searchable: { type: Boolean, default: true },
    taggable: { type: Boolean, default: false },
    options: { type: Array, default: null },
    flat: { type: Boolean, default: false },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const anchorClasses = cva({
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

// todo: hmm, the search query is set to the current value of the input, probably not what we want...
const searchQuery = ref('');

const results = computed(() => {
    if (!props.searchable) {
        return props.options;
    }

    // Avoid mutating the original options array.
    let options = JSON.parse(JSON.stringify(props.options));

    if (props.taggable && searchQuery.value) {
        options.push({
            label: searchQuery.value,
            value: searchQuery.value,
        })
    }

    return fuzzysort.go(searchQuery.value, options, {
        all: true,
        key: 'label',
    }).map((result) => result.obj);
});
</script>

<template>
    <WithField :label :description>
        <ComboboxRoot v-bind="attrs" :ignore-filter="true" :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
            <ComboboxAnchor :class="[anchorClasses, $attrs.class]" data-ui-typeahead-anchor>
                <ComboboxTrigger as="div" class="w-full">
                    <ComboboxInput class="w-full" v-model="searchQuery" :placeholder :display-value="!multiple ? ((value) => value[0].label) : null" />
                </ComboboxTrigger>
                <div class="flex items-center space-x-2 pl-2">
                    <ComboboxCancel v-if="clearable">
                        <div class="inline-flex p-1 bg-zinc-100 rounded-full">
                            <Icon name="plus" class="rotate-45" />
                        </div>
                    </ComboboxCancel>
                    <ComboboxTrigger class="flex items-center">
                        <Icon name="ui/chevron-down" class="me-2" />
                    </ComboboxTrigger>
                </div>
            </ComboboxAnchor>

            <ComboboxPortal>
                <ComboboxContent
                    position="popper"
                    :side-offset="5"
                    :class="[
                        'shadow-ui-sm z-100 rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-gray-800',
                        'max-h-[var(--reka-combobox-content-available-height)] w-[var(--reka-combobox-trigger-width)]',
                    ]"
                >
                    <ComboboxViewport>
                        <ComboboxEmpty class="text-mauve8 text-xs font-medium text-center py-2" />

                        <ComboboxItem
                            v-if="results"
                            v-for="(option, index) in results"
                            :key="index"
                            :value="option.value"
                            :text-value="option.label"
                            :class="itemClasses"
                        >
                            <slot name="option" v-bind="option">
                                <img v-if="option.image" :src="option.image" class="size-5 rounded-full" />
                                <span v-html="option.label" />
                            </slot>
                        </ComboboxItem>
                    </ComboboxViewport>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>
    </WithField>
</template>
