<script setup>
import { cva } from 'cva';
import { ComboboxAnchor, ComboboxContent, ComboboxEmpty, ComboboxInput, ComboboxItem, ComboboxRoot, ComboboxTrigger, ComboboxPortal, ComboboxViewport } from 'reka-ui';
import { computed, nextTick, onMounted, ref, useAttrs, useSlots, useTemplateRef, watch } from 'vue';
import { Button, Icon, Badge } from '@/components/ui';
import fuzzysort from 'fuzzysort';
import { SortableList } from '@/components/sortable/Sortable.js';

const emit = defineEmits(['update:modelValue', 'search', 'selected', 'added']);

const props = defineProps({
    id: { type: String },
    clearable: { type: Boolean, default: false },
    closeOnSelect: { type: Boolean, default: undefined },
    disabled: { type: Boolean, default: false },
    discreteFocusOutline: { type: Boolean, default: false },
    icon: { type: String, default: null },
    ignoreFilter: { type: Boolean, default: false },
    labelHtml: { type: Boolean, default: false },
    maxSelections: { type: Number, default: null },
    modelValue: { type: [Object, String, Number], default: null },
    multiple: { type: Boolean, default: false },
    optionLabel: { type: String, default: 'label' },
    options: { type: Array, default: null },
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: 'Select...' },
    readOnly: { type: Boolean, default: false },
    searchable: { type: Boolean, default: true },
    size: { type: String, default: 'base' },
    taggable: { type: Boolean, default: false },
    variant: { type: String, default: 'default' },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const triggerClasses = cva({
    base: 'w-full flex items-center justify-between antialiased cursor-pointer',
    variants: {
        variant: {
            default: [
                'bg-linear-to-b from-white to-gray-50 text-gray-900 border border-gray-300 shadow-ui-sm focus-within:focus-outline',
                'dark:from-gray-850 dark:to-gray-900 dark:border-gray-700 dark:text-gray-300 dark:shadow-ui-md',
            ],
            filled: 'bg-black/5 hover:bg-black/10 text-gray-900 border-none dark:bg-white/15 dark:hover:bg-white/20 dark:text-white focus-within:focus-outline dark:placeholder:text-red-500/60',
            ghost: 'bg-transparent hover:bg-gray-400/10 text-gray-900 border-none dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200 focus-within:focus-outline',
            subtle: 'bg-transparent hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 border-none dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200 focus-within:focus-outline',
        },
        size: {
            lg: 'px-6 h-12 text-base rounded-lg',
            base: 'px-4 h-10 text-sm rounded-lg',
            sm: 'px-3 h-8 text-[0.8125rem] rounded-lg',
            xs: 'px-2 h-6 text-xs rounded-md',
        },
        'discrete-focus-outline': {
            true: 'focus-outline-discrete',
        },
        readOnly: {
            true: 'border-dashed',
        },
        disabled: {
            true: 'opacity-50 cursor-not-allowed',
        }
    },
})({
    variant: props.variant,
    size: props.size,
    'discrete-focus-outline': props.discreteFocusOutline,
    readOnly: props.readOnly,
    disabled: props.disabled,
});

const itemClasses = cva({
    base: [
        'w-full flex items-center gap-2 relative select-none cursor-pointer text-sm',
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
            false: 'text-gray-900 dark:text-gray-300 data-highlighted:bg-gray-100 data-highlighted:text-gray-900 dark:data-highlighted:bg-gray-700 dark:data-highlighted:text-gray-300',
            true: 'bg-blue-50 dark:bg-blue-600 text-blue-600! dark:text-blue-50!',
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

const getOptionLabel = (option) => option?.[props.optionLabel];
const getOptionValue = (option) => option?.[props.optionValue];
const isSelected = (option) => selectedOptions.value.filter((item) => getOptionValue(item) === getOptionValue(option)).length > 0;

const limitReached = computed(() => {
    if (! props.maxSelections) return false;

    return selectedOptions.value.length >= props.maxSelections;
});

const limitExceeded = computed(() => {
    if (! props.maxSelections) return false;

    return selectedOptions.value.length > props.maxSelections;
});

const limitIndicatorColor = computed(() => {
    if (limitExceeded.value) {
        return 'text-red-600';
    } else if (limitReached.value) {
        return 'text-green-600';
    }

    return 'text-gray';
});

const searchQuery = ref('');
const searchInputRef = useTemplateRef('search');

watch(searchQuery, (value) => {
    emit('search', value, () => {});
});

const filteredOptions = computed(() => {
    if (!props.searchable || props.ignoreFilter) {
        return props.options;
    }

    const options = JSON.parse(JSON.stringify(props.options));

    const results = fuzzysort
        .go(searchQuery.value, options, {
            all: true,
            key: props.optionLabel,
        })
        .map((result) => result.obj);

    if (props.taggable && searchQuery.value && results.length === 0) {
        results.push({
            [props.optionLabel]: searchQuery.value,
            [props.optionValue]: searchQuery.value,
        });
    }

    return results;
});

function clear() {
    searchQuery.value = '';
    emit('update:modelValue', null);
}

function deselect(option) {
    emit('update:modelValue', props.modelValue.filter((item) => item !== option));
}

const dropdownOpen = ref(false);
const closeOnSelect = computed(() => props.closeOnSelect || !props.multiple);

function updateDropdownOpen(open) {
    // Prevent dropdown from opening when it's a taggable combobox with no options.
    if (props.taggable && props.options.length === 0) {
        return;
    }

    dropdownOpen.value = open;
}

function updateModelValue(value) {
    let originalValue = props.modelValue || [];

    searchQuery.value = '';
    emit('update:modelValue', value);

    if (!Array.isArray(value)) value = [value];
    if (!Array.isArray(originalValue)) originalValue = [originalValue];

    value
        .filter((option) => !originalValue?.includes(option))
        .forEach((option) => emit('selected', option));
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
        if (e.target.value === '') return;

        if (props.modelValue.includes(e.target.value)) {
            searchQuery.value = '';
            return;
        }

        emit('added', e.target.value);

        updateModelValue([...props.modelValue, e.target.value]);
    }
}

function openOnSpace(e) {
	const target = e && e.target ? e.target : null;
	const tag = (target && target.tagName ? target.tagName : '').toLowerCase();
	const isEditable = target && (tag === 'input' || tag === 'textarea' || target.isContentEditable);

	// If already open, do nothing so Space behaves normally
	if (dropdownOpen.value) return;

	// If focused element is editable but dropdown is closed, intercept to open
	if (isEditable) {
		if (e && typeof e.preventDefault === 'function') e.preventDefault();
		updateDropdownOpen(true);
		nextTick(() => {
			const inputEl = searchInputRef?.value?.$el || searchInputRef?.value;
			if (inputEl && typeof inputEl.focus === 'function') inputEl.focus();
		});
		return;
	}

	// Non-editable target and closed: open
	if (e && typeof e.preventDefault === 'function') e.preventDefault();
	updateDropdownOpen(true);
	nextTick(() => {
		const inputEl = searchInputRef?.value?.$el || searchInputRef?.value;
		if (inputEl && typeof inputEl.focus === 'function') inputEl.focus();
	});
}

defineExpose({
    searchQuery,
    filteredOptions,
});
</script>

<template>
    <div>
        <div class="flex">
            <ComboboxRoot
                :disabled="disabled || (multiple && limitReached) || readOnly"
                :model-value="modelValue"
                :multiple
                :open="dropdownOpen"
                :reset-search-term-on-blur="false"
                :reset-search-term-on-select="false"
                @update:model-value="updateModelValue"
                @update:open="updateDropdownOpen"
                class="cursor-pointer"
                data-ui-combobox
                ignore-filter
                v-bind="attrs"
            >
                <ComboboxAnchor :class="[$attrs.class]" data-ui-combobox-anchor>
                    <ComboboxTrigger as="div" ref="trigger" :class="triggerClasses" @keydown.space="openOnSpace" data-ui-combobox-trigger>
                        <div class="flex-1 min-w-0">
                            <ComboboxInput
                                v-if="searchable && (dropdownOpen || !modelValue || (multiple && placeholder))"
                                ref="search"
                                class="w-full bg-transparent text-gray-900 dark:text-gray-300 opacity-100 focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 [&::-webkit-search-cancel-button]:hidden"
                                type="search"
                                :id="id"
                                v-model="searchQuery"
                                :placeholder
                                autocomplete="off"
                                @paste.prevent="onPaste"
                                @keydown.enter.prevent="pushTaggableOption"
                                @blur="pushTaggableOption"
                                @keydown.space="openOnSpace"
                            />

                            <button type="button" class="w-full text-start truncate bg-transparent cursor-pointer" v-else-if="!searchable && (dropdownOpen || !modelValue)" @keydown.space="openOnSpace" data-ui-combobox-placeholder>
                                <span class="text-gray-400 dark:text-gray-500" v-text="placeholder" />
                            </button>

                            <button type="button" v-else class="w-full text-start bg-transparent truncate flex items-center gap-2 cursor-pointer" @keydown.space="openOnSpace" data-ui-combobox-selected-option>
                                <slot name="selected-option" v-bind="{ option: selectedOption }">
                                    <Icon v-if="icon" :name="icon" class="text-white-400 dark:text-white dark:opacity-50" />
                                    <span v-if="labelHtml" v-html="getOptionLabel(selectedOption)" />
                                    <span v-else v-text="getOptionLabel(selectedOption)" />
                                </slot>
                            </button>
                        </div>

                        <div class="flex gap-1.5 items-center shrink-0 ms-1.5">
                            <Button v-if="clearable && modelValue" icon="x" variant="ghost" size="xs" round @click="clear" data-ui-combobox-clear-button />
                            <Icon v-if="options.length || ignoreFilter" name="ui/chevron-down" class="text-gray-400 dark:text-white/40" data-ui-combobox-chevron />
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
                            '[&_[data-reka-combobox-viewport]]:grid [&_[data-reka-combobox-viewport]]:gap-1'
                        ]"
                        @escape-key-down="nextTick(() => $refs.trigger.$el.focus())"
                        data-ui-combobox-content
                    >
                        <ComboboxViewport>
                            <ComboboxEmpty class="p-2 text-sm" data-ui-combobox-empty>
                                <slot name="no-options" v-bind="{ searchQuery }">
                                    {{ __('No options available.') }}
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
                                data-ui-combobox-item
                                @select="() => {
                                    dropdownOpen = !closeOnSelect;
                                    if (closeOnSelect) $refs.trigger.$el.focus();
                                }"
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

            <div v-if="maxSelections && maxSelections !== Infinity && multiple" class="ms-2 mt-3 text-xs" :class="limitIndicatorColor" data-ui-combobox-limit-indicator>
                <span v-text="selectedOptions.length"></span>/<span v-text="maxSelections"></span>
            </div>
        </div>

        <slot name="selected-options" v-bind="{ disabled, readOnly, getOptionLabel, getOptionValue, labelHtml, deselect }">
            <sortable-list
                v-if="multiple"
                data-ui-combobox-selected-options
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
                                class="opacity-75 hover:opacity-100 cursor-pointer"
                                :aria-label="__('Deselect option')"
                                @click="deselect(option.value)"
                            >
                                &times;
                            </button>
                            <button v-else type="button" class="opacity-75">
                                &times;
                            </button>
                        </Badge>
                    </div>
                </div>
            </sortable-list>
        </slot>
    </div>
</template>
