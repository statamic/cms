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
    ComboboxVirtualizer,
    FocusScope
} from 'reka-ui';
import { computed, nextTick, ref, useAttrs, useTemplateRef, watch } from 'vue';
import { twMerge } from 'tailwind-merge';
import Button from '../Button/Button.vue';
import Icon from '../Icon/Icon.vue';
import Badge from '../Badge.vue';
import fuzzysort from 'fuzzysort';
import DOMPurify from 'dompurify';
import { SortableList } from '@/components/sortable/Sortable.js';

const emit = defineEmits(['update:modelValue', 'search', 'selected', 'added']);

const props = defineProps({
	/** When `true`, the dropdown will expand to fit longer option labels. Not recommended for large datasets. */
	adaptiveWidth: { type: Boolean, default: false },
	/** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
	align: { type: String, default: 'start' },
	/** When `true`, the selected value will be clearable. */
	clearable: { type: Boolean, default: false },
	/** When `true`, the options dropdown will close after selecting an option. */
	closeOnSelect: { type: Boolean, default: undefined },
	disabled: { type: Boolean, default: false },
	/** When `true`, the focus outline will be more discrete. */
	discreteFocusOutline: { type: Boolean, default: false },
	/** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
	icon: { type: String, default: null },
	/** ID attribute for the input element */
	id: { type: String },
	/** When `true`, the Combobox will avoid filtering options, allowing you to handle filtering yourself by listening to the `search` event and updating the `options` prop. */
	ignoreFilter: { type: Boolean, default: false },
	/** When `true`, the option labels will be rendered with `v-html` instead of `v-text`. */
	labelHtml: { type: Boolean, default: false },
	/** The maximum number of selectable options. */
	maxSelections: { type: Number, default: null },
	/** The controlled value of the combobox. */
	modelValue: { type: [Object, String, Number], default: null },
	/** When `true`, multiple options are allowed. */
	multiple: { type: Boolean, default: false },
	/** Key of the option's label in the option's object. */
	optionLabel: { type: String, default: 'label' },
	/** Array of option objects */
	options: { type: Array, default: () => [] },
	/** Key of the option's value in the option's object. */
	optionValue: { type: String, default: 'value' },
	placeholder: { type: String, default: () => __('Select...') },
	readOnly: { type: Boolean, default: false },
	/** When `true`, the options will be searchable. */
	searchable: { type: Boolean, default: true },
	/** Determines if the dropdown should open */
	shouldOpenDropdown: { type: Function, default: () => true },
	/** Controls the size of the combobox. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
	size: { type: String, default: 'base' },
	/** When `true`, additional options can be added by typing in the search input and pressing enter. */
	taggable: { type: Boolean, default: false },
	/** Controls the appearance of the combobox. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
	variant: { type: String, default: 'default' },
});

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const wrapperClasses = computed(() => twMerge('w-full min-w-0', attrs.class));
const wrapperAttrs = computed(() => {
    const { class: _, ...rest } = attrs;
    return rest;
});

const triggerClasses = cva({
    base: 'w-full flex items-center justify-between antialiased cursor-pointer',
    variants: {
        variant: {
            default: [
                'bg-linear-to-b from-white to-gray-50 text-gray-900 border border-gray-300 with-contrast:border-gray-500 shadow-ui-sm focus-within:focus-outline',
                'dark:from-gray-850 dark:to-gray-900 dark:border-gray-700 dark:text-gray-300 dark:shadow-ui-md',
            ],
            filled: 'bg-gray-950/5 hover:bg-gray-950/10 text-gray-900 border-none dark:bg-white/15 dark:hover:bg-white/20 dark:text-white focus-within:focus-outline dark:placeholder:text-red-500/60',
            ghost: 'bg-transparent hover:bg-gray-400/10 text-gray-900 border-none dark:text-gray-300 dark:hover:bg-white/7 dark:hover:text-gray-200 focus-within:focus-outline',
            subtle: 'bg-transparent hover:bg-gray-400/10 text-gray-500 hover:text-gray-700 border-none dark:text-gray-300 dark:hover:bg-white/7 dark:hover:text-gray-200 focus-within:focus-outline',
        },
        size: {
            xl: 'px-5 h-12 text-lg rounded-lg',
            lg: 'px-4 h-12 text-base rounded-lg',
            base: 'px-4 h-10 text-md rounded-lg',
            sm: 'px-3 h-8 text-sm rounded-lg',
            xs: 'px-2 h-6 text-[0.8125rem] rounded-md',
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
        'w-full flex items-center gap-2 relative select-none cursor-pointer text-sm overflow-hidden',
        'py-1.5 px-2 antialiased rounded-lg',
        'data-disabled:text-gray-300 dark:data-disabled:text-gray-500 data-disabled:pointer-events-none data-highlighted:outline-hidden',
    ],
    variants: {
        size: {
            base: '',
            sm: 'text-sm',
            xs: 'text-xs',
        },
        selected: {
            false: 'text-gray-900 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 data-highlighted:bg-gray-100 dark:data-highlighted:bg-gray-700',
            true: 'bg-blue-50 dark:bg-blue-600 text-blue-600! dark:text-blue-50!',
        },
    },
});

const searchQuery = ref('');
const dropdownOpen = ref(false);
const rootRef = useTemplateRef('root');
const triggerRef = useTemplateRef('trigger');
const searchInputRef = useTemplateRef('search');

watch(searchQuery, (value) => emit('search', value, () => {}));
watch(dropdownOpen, () => searchQuery.value = '');

const getOptionLabel = (option) => {
    const label = option?.[props.optionLabel];
    if (props.labelHtml) {
        return DOMPurify.sanitize(label ?? '', {
            USE_PROFILES: { html: true, svg: true },
        });
    }
    return label;
};

const getOptionValue = (option) => option?.[props.optionValue];
const matchOptionByValue = (option, value) => getOptionValue(option) === value;
const isSelected = (option) => selectedOptions.value.some((item) => getOptionValue(item) === getOptionValue(option));
const isDisabled = (option) => !isSelected(option) && props.multiple && limitReached.value;

const selectedOptions = computed(() => {
    let selections = props.modelValue === null ? [] : props.modelValue;

    if (typeof selections === 'string' || typeof selections === 'number') {
        selections = [selections];
    }

    return selections.map((value) => {
        return props.options.find((option) => getOptionValue(option) === value)
            ?? { [props.optionLabel]: value, [props.optionValue]: value };
    });
});

const selectedOption = computed(() => {
    if (props.multiple || !props.modelValue || selectedOptions.value.length !== 1) {
        return null;
    }

    return selectedOptions.value[0];
});

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

const canClearSelection = computed(() => props.clearable && props.modelValue);
const shouldCloseOnSelect = computed(() => props.closeOnSelect ?? !props.multiple);
const shouldShowOptionsChevron = computed(() => props.options.length > 0 || props.ignoreFilter);
const shouldShowLimitIndicator = computed(() => props.multiple && props.maxSelections && props.maxSelections !== Infinity);

const shouldShowInput = computed(() => {
    if (!props.searchable) return false;
    if (props.taggable) return true;

    return dropdownOpen.value || !props.modelValue || (props.multiple && props.placeholder);
});

const placeholder = computed(() => {
    if (props.multiple && selectedOptions.value.length > 0) {
        return __n(':count item selected|:count items selected', selectedOptions.value.length);
    }

    if (selectedOption.value) {
        return getOptionLabel(selectedOption.value);
    }

    return props.placeholder;
});

const filteredOptions = computed(() => {
    if (!props.searchable || props.ignoreFilter) {
        return props.options;
    }

    const matches = new Set(
        fuzzysort
            .go(searchQuery.value, props.options, {
                all: true,
                key: props.optionLabel,
            })
            .map((result) => result.obj)
    );

    const results = props.options.filter((option) => matches.has(option));

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

function select() {
    dropdownOpen.value = !shouldCloseOnSelect.value;
    if (shouldCloseOnSelect.value) triggerRef.value?.$el?.focus();
}

function deselect(option) {
    emit('update:modelValue', props.modelValue.filter((item) => item !== option));
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

function updateDropdownOpen(open) {
    if (open && ! props.shouldOpenDropdown(open)) return;
    if (props.disabled || props.readOnly) return;

    dropdownOpen.value = open;

    if (open) {
        nextTick(() => searchInputRef?.value?.$el?.focus());
        requestAnimationFrame(() => requestAnimationFrame(() => scrollToSelectedOption()));
    }
}

function openDropdown(e) {
    if (dropdownOpen.value) return;
    if (e.key === ' ' && e.target.tagName === 'INPUT') return;
    if (typeof e.preventDefault === 'function') e.preventDefault();

    updateDropdownOpen(true);
}

function onBlur(e) {
    if (!props.taggable) return;

    let isInsideDropdown = 'rekaCollectionItem' in (e.relatedTarget?.dataset ?? {});
    if (isInsideDropdown) return;

    pushTaggableOption(e);
}

function onPaste(e) {
    if (!props.taggable) return;

    e.preventDefault();

    const pastedValue = e.clipboardData.getData('text');

    updateModelValue([...(props.modelValue ?? []), ...pastedValue.split(',').map((v) => v.trim())]);
}

function pushTaggableOption(e) {
    if (!props.taggable) return;
    if (e.target.value === '') return;

    e.preventDefault();

    if (props.modelValue?.includes(e.target.value)) {
        searchQuery.value = '';
        return;
    }

    emit('added', e.target.value);

    updateModelValue([...props.modelValue ?? [], e.target.value]);
}

function scrollToSelectedOption() {
    if (props.multiple || !props.modelValue) return;

    rootRef.value?.highlightSelected?.();
}

function focus() {
    shouldShowInput.value
        ? nextTick(() => searchInputRef.value?.$el.focus())
        : nextTick(() => triggerRef.value?.$el.focus());
}

defineExpose({
    searchQuery,
    filteredOptions,
    focus,
});
</script>

<template>
    <div :class="wrapperClasses" v-bind="wrapperAttrs">
        <div class="flex w-full min-w-0">
            <ComboboxRoot
                ref="root"
                class="cursor-pointer flex-1 min-w-0"
                :multiple
                :open="dropdownOpen"
                :model-value="modelValue"
                :by="matchOptionByValue"
                :disabled="disabled || readOnly"
                :reset-search-term-on-blur="false"
                :reset-search-term-on-select="false"
                data-ui-combobox
                ignore-filter
                @update:open="updateDropdownOpen"
                @update:model-value="updateModelValue"
            >
                <ComboboxAnchor class="block w-full" data-ui-combobox-anchor>
                    <ComboboxTrigger
                        as="div"
                        ref="trigger"
                        :tabindex="disabled || readOnly ? -1 : 0"
                        :class="triggerClasses"
                        data-ui-combobox-trigger
                        @keydown.enter="openDropdown"
                        @keydown.space="openDropdown"
                    >
                        <div class="flex-1 min-w-0">
                            <ComboboxInput
                                v-if="shouldShowInput"
                                :id
                                :placeholder
                                ref="search"
                                class="w-full bg-transparent text-gray-900 dark:text-gray-300 opacity-100 focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 [&::-webkit-search-cancel-button]:hidden cursor-pointer"
                                :class="{
                                    'placeholder-gray-900! dark:placeholder-gray-300!': selectedOption && !multiple && !dropdownOpen
                                }"
                                type="search"
                                autocomplete="off"
                                v-model="searchQuery"
                                @blur="onBlur"
                                @paste="onPaste"
                                @keydown.enter="pushTaggableOption"
                            />

                            <div
                                v-else
                                class="w-full text-start bg-transparent flex items-center gap-2 cursor-pointer focus:outline-none select-none"
                                data-ui-combobox-selected-option
                            >
                                <slot v-if="selectedOption" name="selected-option" v-bind="{ option: selectedOption }">
                                    <div v-if="icon" class="size-4">
                                        <Icon :name="icon" class="text-gray-900 dark:text-white dark:opacity-50" />
                                    </div>
                                    <span v-if="labelHtml" v-html="getOptionLabel(selectedOption)" class="block truncate" />
                                    <span v-else v-text="getOptionLabel(selectedOption)" class="block truncate" />
                                </slot>
                                <span v-else class="block truncate text-gray-500 dark:text-gray-400" v-text="placeholder" />
                            </div>
                        </div>

                        <div v-if="canClearSelection || shouldShowOptionsChevron" class="flex gap-1.5 items-center ms-1.5 -me-1">
                            <Button
                                v-if="canClearSelection"
                                icon="x"
                                variant="ghost"
                                size="xs"
                                round
                                :disabled="disabled || readOnly"
                                :aria-label="__('Clear selection')"
                                data-ui-combobox-clear-button
                                @click="clear"
                            />
                            <Icon
                                v-if="shouldShowOptionsChevron"
                                name="chevron-down"
                                class="text-gray-400 dark:text-white/40 size-4"
                                aria-hidden="true"
                                data-ui-combobox-chevron
                            />
                        </div>
                    </ComboboxTrigger>
                </ComboboxAnchor>

                <ComboboxPortal>
                    <ComboboxContent
                        :align
                        :side-offset="5"
                        position="popper"
                        :class="[
                            'shadow-ui-sm z-(--z-index-above) rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-800',
                            'max-h-[var(--reka-combobox-content-available-height)] min-w-[var(--reka-combobox-trigger-width)] overflow-hidden',
                            adaptiveWidth && 'w-max max-w-md',
                        ]"
                        data-ui-combobox-content
                        @escape-key-down="focus"
                    >
                        <FocusScope
                            :trapped="!searchable"
                            @mount-auto-focus.prevent
                            @unmount-auto-focus="(event) => {
                                if (event.defaultPrevented) return;
                                focus();
                                event.preventDefault();
                            }"
                        >
                            <div class="relative max-h-[300px] overflow-y-auto py-2" data-ui-combobox-viewport>
                                <!-- Hidden width measurer for wide dropdown mode -->
                                <div v-if="adaptiveWidth" aria-hidden="true" class="h-0 overflow-y-clip px-2">
                                    <div v-for="option in filteredOptions" :key="getOptionValue(option)" class="py-1.5 px-2 text-sm whitespace-nowrap">
                                        {{ getOptionLabel(option) }}
                                    </div>
                                </div>

                                <ComboboxEmpty class="py-1 px-4 text-sm" role="status" aria-live="polite" data-ui-combobox-empty>
                                    <slot name="no-options" v-bind="{ searchQuery }">
                                        {{ __('No options available.') }}
                                    </slot>
                                </ComboboxEmpty>

                                <ComboboxVirtualizer
                                    v-if="filteredOptions.length"
                                    :estimate-size="40"
                                    :options="filteredOptions"
                                    :text-content="(opt) => getOptionLabel(opt)"
                                    v-slot="{ option }"
                                >
                                    <div class="py-1 px-2 w-full overflow-x-hidden">
                                        <ComboboxItem
                                            as="button"
                                            :key="`${getOptionValue(option)}-${isDisabled(option)}`"
                                            :value="getOptionValue(option)"
                                            :text-value="getOptionLabel(option)"
                                            :disabled="isDisabled(option)"
                                            :class="itemClasses({ size: size, selected: isSelected(option) })"
                                            :data-ui-combobox-item="getOptionValue(option)"
                                            :title="getOptionLabel(option)"
                                            @select="select"
                                        >
                                            <slot name="option" v-bind="option">
                                                <img v-if="option.image" :src="option.image" class="size-5 rounded-full" :alt="getOptionLabel(option)">
                                                <span v-if="labelHtml" class="truncate" v-html="getOptionLabel(option)" />
                                                <span class="truncate" v-else>{{ __(getOptionLabel(option)) }}</span>
                                            </slot>
                                        </ComboboxItem>
                                    </div>
                                </ComboboxVirtualizer>
                            </div>
                        </FocusScope>
                    </ComboboxContent>
                </ComboboxPortal>
            </ComboboxRoot>

            <div
                v-if="shouldShowLimitIndicator"
                class="ms-2 mt-3 text-xs"
                :class="limitIndicatorColor"
                :aria-label="__(':count of :max selections', { count: selectedOptions.length, max: maxSelections })"
                aria-live="polite"
                data-ui-combobox-limit-indicator
            >
                <span v-text="selectedOptions.length"></span>/<span v-text="maxSelections"></span>
            </div>
        </div>

        <slot name="selected-options" v-bind="{ disabled, readOnly, getOptionLabel, getOptionValue, labelHtml, deselect }">
            <SortableList
                v-if="multiple"
                data-ui-combobox-selected-options
                item-class="sortable-item"
                handle-class="sortable-item"
                :distance="5"
                :mirror="false"
                :disabled="disabled || readOnly"
                :model-value="modelValue"
                @update:modelValue="updateModelValue"
            >
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="option in selectedOptions"
                        :key="getOptionValue(option)"
                        class="sortable-item mt-2 cursor-grab active:cursor-grabbing"
                    >
                        <Badge pill size="lg" class="[&>*]:st-text-trim-ex-alphabetic">
                            <div v-if="labelHtml" v-html="getOptionLabel(option)"></div>
                            <div v-else>{{ __(getOptionLabel(option)) }}</div>

                            <button
                                v-if="!disabled && !readOnly"
                                type="button"
                                class="opacity-75 hover:opacity-100 cursor-pointer"
                                :aria-label="__('Remove :label', { label: getOptionLabel(option) })"
                                @click="deselect(getOptionValue(option))"
                            >
                                &times;
                            </button>
                            <span v-else class="opacity-75" aria-hidden="true">
                                &times;
                            </span>
                        </Badge>
                    </div>
                </div>
            </SortableList>
        </slot>
    </div>
</template>

<style scoped>
    @supports(text-box: ex alphabetic) {
        [data-ui-badge] {
            padding-block: 0.65rem;
        }
    }
</style>
