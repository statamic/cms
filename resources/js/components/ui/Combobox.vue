<script setup>
import { cva } from 'cva';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxPortal,
    ComboboxViewport,
} from 'reka-ui';
import { computed, nextTick, ref, useAttrs, useTemplateRef, watch } from 'vue';
import { Button, Icon, Badge } from '@statamic/ui';
import fuzzysort from 'fuzzysort';
import { SortableList } from '@statamic/components/sortable/Sortable.js';

const emit = defineEmits(['update:modelValue', 'search']);

const props = defineProps({
    modelValue: { type: [Object, String, Number], default: null },
    size: { type: String, default: 'base' },
    placeholder: { type: String, default: 'Select...' },
    multiple: { type: Boolean, default: false },
    clearable: { type: Boolean, default: false },
    searchable: { type: Boolean, default: true },
    taggable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    maxSelections: { type: Number, default: null },
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    labelHtml: { type: Boolean, default: false },
    ignoreFilter: { type: Boolean, default: false },
    options: { type: Array, default: null },
    flat: { type: Boolean, default: false },
    buttonAppearance: { type: Boolean, default: true },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const triggerClasses = cva({
    base: 'min-h-full w-full flex items-center',
    variants: {
        size: {
            base: 'text-base rounded-lg ps-3 pe-2.5 py-2 h-10 leading-[1.375rem]',
            sm: 'text-sm rounded-md ps-2.5 pe-2 py-1.5 h-7 leading-[1.125rem]',
            xs: 'text-xs rounded-sm ps-2 pe-1.5 py-1.5 h-6 leading-[1.125rem]',
        },
        flat: {
            true: 'shadow-none',
            false: 'bg-linear-to-b from-white to-gray-50 hover:to-gray-100 dark:from-gray-800/30 dark:to-gray-800 dark:hover:to-gray-850 shadow-ui-sm',
        },
        buttonAppearance: {
            true: 'border border-gray-300 dark:border-b-0 dark:ring-3 dark:ring-gray-900 dark:border-white/15 shadow-ui-sm dark:shadow-md',
            false: '',
        },
    },
})({
    size: props.size,
    flat: props.flat,
    buttonAppearance: props.buttonAppearance
});

const itemClasses = cva({
    base: [
        'w-full flex items-center gap-2 relative select-none cursor-pointer',
        'py-1.5 px-2 antialiased rounded-lg',
        'data-disabled:text-gray-300 data-disabled:pointer-events-none data-highlighted:outline-hidden',
    ],
    variants: {
        size: {
            base: '',
            sm: 'text-sm',
            xs: 'text-xs',
        },
        selected: {
            false: 'text-gray-800 dark:text-gray-300 data-highlighted:bg-gray-100 data-highlighted:text-gray-900 dark:data-highlighted:bg-gray-700 dark:data-highlighted:text-gray-300',
            true: 'bg-blue-50 text-blue-600!',
        },
    },
});

const selectedOptions = computed(() => {
    let selections = props.modelValue === null ? [] : props.modelValue;

    if (typeof selections === 'string' || typeof selections === 'number') {
        selections = [selections];
    }

    return selections.map((value) => {
        return props.options.find((option) => getOptionValue(option) === value) ?? { label: value, value };
    });
});

const selectedOption = computed(() => {
    if (props.multiple || !props.modelValue || selectedOptions.value.length !== 1) {
        return null;
    }

    return selectedOptions.value[0];
});

function getOptionLabel(option) {
    if (!option) {
        return;
    }

    return option[props.optionLabel];
}

function getOptionValue(option) {
    if (!option) {
        return;
    }

    return option[props.optionValue];
}

function isSelected(option) {
    return selectedOptions.value.filter((item) => getOptionValue(item) === getOptionValue(option)).length > 0;
}

const limitReached = computed(() => {
    if (!props.maxSelections) {
        return false;
    }

    return selectedOptions.value.length >= props.maxSelections;
});

const limitExceeded = computed(() => {
    if (!props.maxSelections) {
        return false;
    }

    return selectedOptions.value.length > props.maxSelections;
});

const limitIndicatorColor = computed(() => {
    if (limitExceeded.value) {
        return 'text-red-500';
    } else if (limitReached.value) {
        return 'text-green-600';
    }

    return 'text-gray';
});

const searchQuery = ref('');

const filteredOptions = computed(() => {
    if (!props.searchable || props.ignoreFilter) {
        return props.options;
    }

    let options = JSON.parse(JSON.stringify(props.options));

    if (props.taggable && searchQuery.value) {
        options.push({
            [props.optionLabel]: searchQuery.value,
            [props.optionValue]: searchQuery.value,
        });
    }

    return fuzzysort
        .go(searchQuery.value, options, {
            all: true,
            key: props.optionLabel,
        })
        .map((result) => result.obj);
});

watch(searchQuery, (value) => {
    emit('search', value, () => {});
});

const inputRef = useTemplateRef('input');

function clear() {
    searchQuery.value = '';
    emit('update:modelValue', null);

    if (props.searchable) {
        nextTick(() => {
            inputRef.value.$el.focus();
        });
    }
}

function deselect(option) {
    emit(
        'update:modelValue',
        props.modelValue.filter((item) => item !== option),
    );
}

const dropdownOpen = ref(false);

function updateDropdownOpen(open) {
    // Prevent dropdown from opening when it's a taggable combobox with no options.
    if (props.taggable && props.options.length === 0) {
        return;
    }

    dropdownOpen.value = open;
}

function updateModelValue(value) {
    searchQuery.value = '';
    emit('update:modelValue', value);
}

function onPaste(e) {
    if (!props.taggable) {
        return;
    }

    const pastedValue = e.clipboardData.getData('text');

    updateModelValue([...props.modelValue, ...pastedValue.split(',').map((v) => v.trim())]);
}

// When it's a taggable combobox with no options, we need to push the value here as updateModelValue won't be called.
function pushTaggableOption(e) {
    if (props.taggable && props.options.length === 0) {
        if (props.modelValue.includes(e.target.value)) {
            searchQuery.value = '';
            return;
        }

        updateModelValue([...props.modelValue, e.target.value]);
    }
}
</script>

<template>
    <div class="flex">
        <ComboboxRoot
            class="cursor-pointer"
            v-bind="attrs"
            ignore-filter
            :multiple
            :reset-search-term-on-blur="false"
            :reset-search-term-on-select="false"
            :disabled="disabled || (multiple && limitReached)"
            :open="dropdownOpen"
            :model-value="modelValue"
            @update:open="updateDropdownOpen"
            @update:model-value="updateModelValue"
        >
            <ComboboxAnchor :class="['focus-within:focus-outline w-full flex items-center justify-between gap-2 text-gray-800 dark:text-gray-300 antialiased appearance-none', $attrs.class]" data-ui-combobox-anchor>
                <ComboboxTrigger as="div" :class="triggerClasses">
                    <ComboboxInput
                        v-if="searchable && (dropdownOpen || !modelValue || (multiple && placeholder))"
                        ref="input"
                        class="w-full text-gray-700 opacity-100 focus:outline-none"
                        v-model="searchQuery"
                        :placeholder
                        @paste.prevent="onPaste"
                        @keydown.enter.prevent="pushTaggableOption"
                    />
                    <button type="button" class="flex-1 text-start" v-else-if="!searchable && (dropdownOpen || !modelValue)">
                        <span class="text-gray-400 dark:text-gray-500" v-text="placeholder" />
                    </button>
                    <button type="button" v-else class="flex-1 text-start cursor-pointer">
                        <slot name="selected-option" v-bind="{ option: selectedOption }">
                            <span v-if="labelHtml" v-html="getOptionLabel(selectedOption)" />
                            <span v-else v-text="getOptionLabel(selectedOption)" />
                        </slot>
                    </button>

                    <div class="flex gap-1 items-center">
                        <Button icon="x" variant="filled" size="xs" round v-if="clearable && modelValue" @click="clear" />
                        <Icon name="ui/chevron-down" />
                    </div>
                </ComboboxTrigger>
            </ComboboxAnchor>

            <ComboboxPortal>
                <ComboboxContent
                    position="popper"
                    :side-offset="5"
                    :class="[
                        'shadow-ui-sm z-100 rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-gray-800',
                        'max-h-[var(--reka-combobox-content-available-height)] w-[var(--reka-combobox-trigger-width)] min-w-fit',
                    ]"
                >
                    <ComboboxViewport>
                        <ComboboxEmpty class="py-2 text-sm">
                            <slot name="no-options">
                                {{ __('No options to choose from.') }}
                            </slot>
                        </ComboboxEmpty>

                        <ComboboxItem
                            v-if="filteredOptions"
                            v-for="(option, index) in filteredOptions"
                            :key="index"
                            :value="getOptionValue(option)"
                            :text-value="getOptionLabel(option)"
                            :class="itemClasses({ size: size, selected: isSelected(option) })"
                            as="button"
                            @select="dropdownOpen = false"
                        >
                            <slot name="option" v-bind="option">
                                <img v-if="option.image" :src="option.image" class="size-5 rounded-full" />
                                <span v-if="labelHtml" v-html="getOptionLabel(option)" />
                                <span v-else>{{ __(getOptionLabel(option)) }}</span>
                            </slot>
                        </ComboboxItem>
                    </ComboboxViewport>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>

        <div v-if="maxSelections && maxSelections !== Infinity" class="ms-2 mt-3 text-xs" :class="limitIndicatorColor">
            <span v-text="selectedOptions.length"></span>/<span v-text="maxSelections"></span>
        </div>
    </div>

    <slot name="selected-options" v-bind="{ disabled, getOptionLabel, getOptionValue, labelHtml, deselect }">
        <sortable-list
            v-if="multiple"
            item-class="sortable-item"
            handle-class="sortable-item"
            :distance="5"
            :mirror="false"
            :disabled
            :model-value="modelValue"
            @update:modelValue="updateModelValue"
        >
            <div class="flex flex-wrap gap-2">
                <div
                    v-for="option in selectedOptions"
                    :key="getOptionValue(option)"
                    class="sortable-item mt-2"
                >
                    <Badge pill size="lg">
                        <div v-if="labelHtml" v-html="getOptionLabel(option)"></div>
                        <div v-else>{{ __(getOptionLabel(option)) }}</div>

                        <button
                            v-if="!disabled"
                            type="button"
                            class="vs__deselect"
                            :aria-label="__('Deselect option')"
                            @click="deselect(option.value)"
                        >
                            <span>×</span>
                        </button>
                        <button v-else type="button" class="vs__deselect">
                            <span class="text-gray-300">×</span>
                        </button>
                    </Badge>
                </div>
            </div>
        </sortable-list>
    </slot>
</template>
