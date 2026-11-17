<script setup lang="ts">
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
import { computed, nextTick, ref, toRef, useAttrs, useSlots, useTemplateRef, watch } from 'vue';
import { twMerge } from 'tailwind-merge';
import Button from '../Button/Button.vue';
import Icon from '../Icon/Icon.vue';
import Badge from '../Badge.vue';
import fuzzysort from 'fuzzysort';
import DOMPurify, { type Config } from 'dompurify';
import { SortableList } from '@/components/sortable/Sortable.js';
import { useUiFieldId } from '@/composables/ui-field-id.js';

type OptionValue = string | number;

type ModelValue = OptionValue | OptionValue[] | null;

interface Option {
    /** Icon name displayed next to the label. */
    icon?: string;
    /** Image URL displayed next to the label. */
    image?: string;
    /** Set on the "Add" option shown while searching a `taggable` combobox. */
    create?: boolean;
    /** The label and value live under the keys set by `optionLabel` and `optionValue`. */
    [key: string]: any;
}

interface Props {
    /** When `true`, the dropdown will expand to fit longer option labels. Not recommended for large datasets. */
    adaptiveWidth?: boolean;
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align?: 'start' | 'center' | 'end';
    /** When `true`, the selected value will be clearable. */
    clearable?: boolean;
    /** When `true`, the options dropdown will close after selecting an option. */
    closeOnSelect?: boolean;
    disabled?: boolean;
    /** When `true`, the focus outline will be more discrete. */
    discreteFocusOutline?: boolean;
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon?: string | null;
    /** ID attribute for the input element. Inherits from parent `Field` when omitted. */
    id?: string | null;
    /** When `true`, the Combobox will avoid filtering options, allowing you to handle filtering yourself by listening to the `search` event and updating the `options` prop. */
    ignoreFilter?: boolean;
    /** When `true`, the option labels will be rendered with `v-html` instead of `v-text`. */
    labelHtml?: boolean;
    /** The maximum number of selectable options. */
    maxSelections?: number | null;
    /** The controlled value of the combobox. A single option value, or an array of option values when `multiple` is `true`. */
    modelValue?: ModelValue;
    /** When `true`, multiple options are allowed. */
    multiple?: boolean;
    /** Key of the option's label in the option's object. */
    optionLabel?: string;
    /** Array of option objects, like `{ label: 'Apple', value: 'apple' }`. The label and value keys can be changed with `optionLabel` and `optionValue`. Options may also have an `icon` name or an `image` URL. */
    options?: Option[];
    /** Key of the option's value in the option's object. */
    optionValue?: string;
    /** The delimiter used to split pasted text into multiple tags. Only used when `taggable` is `true`. */
    pasteDelimiter?: string;
    placeholder?: string;
    readOnly?: boolean;
    /** When `true`, the options will be searchable. */
    searchable?: boolean;
    /** Keys of the option object to search against. Defaults to just `optionLabel`. */
    searchKeys?: string[] | null;
    /** Determines if the dropdown should open */
    shouldOpenDropdown?: (open: boolean) => boolean;
    /** Controls the size of the combobox. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size?: 'xs' | 'sm' | 'base' | 'lg' | 'xl';
    /** When `true`, additional options can be added by typing in the search input and pressing enter. */
    taggable?: boolean;
    /** Controls the appearance of the combobox. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant?: 'default' | 'filled' | 'ghost' | 'subtle';
    /** When `true`, skip the elevated z-index override while a modal/stack is open. Use when this combobox can remain open behind an unrelated overlay (e.g. a confirmation modal). */
    excludeZManipulation?: boolean;
}

const emit = defineEmits<{
    'update:modelValue': [value: ModelValue];
    search: [query: string, loading: () => void];
    selected: [value: OptionValue];
    added: [value: OptionValue];
}>();

const props = withDefaults(defineProps<Props>(), {
    adaptiveWidth: false,
    align: 'start',
    clearable: false,
    closeOnSelect: undefined,
    disabled: false,
    discreteFocusOutline: false,
    icon: null,
    id: null,
    ignoreFilter: false,
    labelHtml: false,
    maxSelections: null,
    modelValue: null,
    multiple: false,
    optionLabel: 'label',
    options: () => [],
    optionValue: 'value',
    pasteDelimiter: ',',
    placeholder: () => __('Select...'),
    readOnly: false,
    searchable: true,
    searchKeys: null,
    shouldOpenDropdown: () => true,
    size: 'base',
    taggable: false,
    variant: 'default',
    excludeZManipulation: false,
});

defineOptions({
    inheritAttrs: false,
});

const { id, labelId, describedBy, invalid } = useUiFieldId(toRef(props, 'id'));

const attrs = useAttrs();
const slots = useSlots();

const wrapperClasses = computed(() => twMerge('w-full min-w-0', attrs.class as string));
const wrapperAttrs = computed(() => {
    const { class: _, ...rest } = attrs;
    return rest;
});

const triggerClasses = computed(() => cva({
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
            true: 'border-dashed read-only-field:not-dark:bg-none read-only-field:not-dark:bg-gray-100 read-only-field:text-gray-600 read-only-field:not-dark:shadow-none dark:read-only-field:text-gray-500',
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
}));

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

const searchQuery = ref<string>('');
const dropdownOpen = ref(false);
const focusedByPointer = ref(false);
const rootRef = useTemplateRef('root');
const wrapperRef = useTemplateRef('wrapper');
const triggerRef = useTemplateRef('trigger');
const searchInputRef = useTemplateRef('search');

watch(searchQuery, (value) => emit('search', value, () => {}));

watch(dropdownOpen, (open) => {
    if (!open) searchQuery.value = '';
});

const labelSanitizeConfig: Config = { USE_PROFILES: { html: true, svg: true } };

const getOptionLabel = (option: Option): string => {
    const label = option?.[props.optionLabel];
    if (props.labelHtml) {
        return DOMPurify.sanitize(label ?? '', labelSanitizeConfig);
    }
    return label;
};

const getOptionPlainLabel = (option: Option): string => {
    if (!props.labelHtml) return getOptionLabel(option);

    return DOMPurify.sanitize(option?.[props.optionLabel] ?? '', { ...labelSanitizeConfig, RETURN_DOM_FRAGMENT: true })
        .textContent.replace(/\s+/g, ' ').trim();
};

const getOptionValue = (option: Option): OptionValue => option?.[props.optionValue];
const matchOptionByValue = (option: Option, value: OptionValue): boolean => getOptionValue(option) === value;
const isSelected = (option: Option): boolean => selectedOptions.value.some((item) => getOptionValue(item) === getOptionValue(option));
const isDisabled = (option: Option): boolean => !isSelected(option) && props.multiple && limitReached.value;

const selectedOptions = computed<Option[]>(() => {
    let selections = props.modelValue === null ? [] : props.modelValue;

    if (typeof selections === 'string' || typeof selections === 'number') {
        selections = [selections];
    }

    return selections.map((value) => {
        return props.options.find((option) => getOptionValue(option) === value)
            ?? { [props.optionLabel]: value, [props.optionValue]: value };
    });
});

const selectedOption = computed<Option | null>(() => {
    if (props.multiple || props.modelValue === null || selectedOptions.value.length !== 1) {
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

const canClearSelection = computed(() => props.clearable && props.modelValue !== null);
const shouldCloseOnSelect = computed(() => props.closeOnSelect ?? !props.multiple);
const shouldShowOptionsChevron = computed(() => props.options.length > 0 || props.ignoreFilter);
const shouldShowLimitIndicator = computed(() => props.multiple && props.maxSelections && props.maxSelections !== Infinity);

const shouldShowInput = computed(() => {
    if (!props.searchable) return false;

    // When taggable, we always want to show the input unless the `selected-option`
    // slot is provided, in which case we only want to show the input when the dropdown is open.
    if (props.taggable) {
        return !!slots['selected-option'] ? dropdownOpen.value : true;
    }

    return dropdownOpen.value || props.modelValue === null || (props.multiple && props.placeholder);
});

// The search input is the combobox whenever it's rendered, so the trigger wrapping it has to give up
// the role, the tab stop and the popup attributes that reka-ui puts on every trigger.
const triggerAttrs = computed(() => shouldShowInput.value
    ? { 'aria-label': undefined, 'aria-haspopup': undefined, 'aria-expanded': undefined }
    : { role: 'combobox', tabindex: props.disabled || props.readOnly ? -1 : 0 });

const placeholder = computed(() => {
    if (props.multiple && selectedOptions.value.length > 0) {
        return __n(':count item selected|:count items selected', selectedOptions.value.length);
    }

    if (selectedOption.value) {
        return getOptionPlainLabel(selectedOption.value);
    }

    return props.placeholder;
});

const filteredOptions = computed<Option[]>(() => {
    let results: Option[] = [...props.options];

    if (props.searchable && !props.ignoreFilter) {
        const searchResults = props.searchKeys?.length
            ? fuzzysort.go(searchQuery.value, props.options, { all: true, keys: props.searchKeys })
            : fuzzysort.go(searchQuery.value, props.options, { all: true, key: props.optionLabel });

        const matches = new Set<Option>(searchResults.map((result) => result.obj));

        results = props.options.filter((option) => matches.has(option));
    }

    if (shouldShowCreateOption.value) {
        results.unshift({
            [props.optionLabel]: searchQuery.value,
            [props.optionValue]: searchQuery.value,
            create: true,
        });
    }

    return results;
});

const shouldShowCreateOption = computed(() => {
    if (!props.taggable || !searchQuery.value) return false;

    const matchesSearchQuery = (option: Option): boolean =>
        getOptionPlainLabel(option) === searchQuery.value || String(getOptionValue(option)) === searchQuery.value;

    return !props.options.some(matchesSearchQuery) && !selectedOptions.value.some(matchesSearchQuery);
});

function clear() {
    searchQuery.value = '';
    emit('update:modelValue', null);
}

function select(option: Option) {
    if (option.create) emit('added', getOptionValue(option));

    dropdownOpen.value = !shouldCloseOnSelect.value;
    if (shouldCloseOnSelect.value) triggerRef.value?.$el?.focus();
}

function deselect(value: OptionValue) {
    emit('update:modelValue', (props.modelValue as OptionValue[]).filter((item) => item !== value));
}

function updateModelValue(value: ModelValue) {
    let originalValue: ModelValue = props.modelValue === null ? [] : props.modelValue;

    searchQuery.value = '';
    emit('update:modelValue', value);

    if (!Array.isArray(value)) value = [value];
    if (!Array.isArray(originalValue)) originalValue = [originalValue];

    value
        .filter((option) => !originalValue?.includes(option))
        .forEach((option) => emit('selected', option));
}

function updateDropdownOpen(open: boolean) {
    if (open && ! props.shouldOpenDropdown(open)) return;
    if (props.disabled || props.readOnly) return;

    dropdownOpen.value = open;

    if (open) {
        nextTick(() => searchInputRef?.value?.$el?.focus());
        requestAnimationFrame(() => requestAnimationFrame(() => scrollToSelectedOption()));
    }
}

function openDropdown(e: KeyboardEvent) {
    if (dropdownOpen.value) return;
    if (e.key === ' ' && (e.target as HTMLElement).tagName === 'INPUT') return;
    if (typeof e.preventDefault === 'function') e.preventDefault();

    updateDropdownOpen(true);
}

function onFocus(e: FocusEvent) {
    const byPointer = focusedByPointer.value;
    focusedByPointer.value = false;

    if (!props.taggable || byPointer || dropdownOpen.value) return;
    if (!focusCameFromOutside(e)) return;

    updateDropdownOpen(true);
}

function focusCameFromOutside(e: FocusEvent): boolean {
    const relatedTarget = e.relatedTarget as HTMLElement | null;

    if (!relatedTarget) return false;
    if ('rekaCollectionItem' in relatedTarget.dataset) return false;

    return !wrapperRef.value?.contains(relatedTarget);
}

function onBlur(e: FocusEvent) {
    if (!props.taggable) return;

    let isInsideDropdown = 'rekaCollectionItem' in ((e.relatedTarget as HTMLElement | null)?.dataset ?? {});
    if (isInsideDropdown) return;

    pushTaggableOption(e);
}

function onPaste(e: ClipboardEvent) {
    if (!props.taggable || !props.multiple) return;

    e.preventDefault();

    const pastedValue = e.clipboardData.getData('text');

    const tags = pastedValue
        .split(props.pasteDelimiter)
        .map((v) => v.trim())
        .filter((v) => v !== '');

    updateModelValue([...((props.modelValue as OptionValue[]) ?? []), ...tags]);
}

function pushTaggableOption(e: KeyboardEvent | FocusEvent) {
    if (!props.taggable) return;
    if (e.defaultPrevented) return; // Reka prevents the event's default when it selects a highlighted option.

    const value = (e.target as HTMLInputElement).value;

    if (value === '') return;

    e.preventDefault();

    const alreadySelected = props.multiple
        ? (props.modelValue as OptionValue[] | null)?.includes(value)
        : props.modelValue === value;

    if (!alreadySelected) {
        emit('added', value);
        updateModelValue(props.multiple ? [...((props.modelValue as OptionValue[]) ?? []), value] : value);
    }

    searchQuery.value = '';

    if (!props.multiple) dropdownOpen.value = false;
}

function scrollToSelectedOption() {
    if (props.multiple || props.modelValue === null) return;

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
    <div
        ref="wrapper"
        :class="wrapperClasses"
        v-bind="wrapperAttrs"
        @pointerdown="focusedByPointer = true"
        @click="focusedByPointer = false"
    >
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
                        v-bind="triggerAttrs"
                        :id="shouldShowInput ? undefined : id"
                        :aria-labelledby="shouldShowInput ? undefined : labelId"
                        :aria-describedby="shouldShowInput ? undefined : describedBy"
                        :aria-invalid="!shouldShowInput && invalid ? 'true' : undefined"
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
                                :aria-describedby="describedBy"
                                :aria-invalid="invalid ? 'true' : undefined"
                                ref="search"
                                class="w-full bg-transparent text-gray-900 dark:text-gray-300 opacity-100 focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 [&::-webkit-search-cancel-button]:hidden cursor-pointer"
                                :class="{
                                    'placeholder-gray-900! dark:placeholder-gray-300!': selectedOption && !multiple && !dropdownOpen
                                }"
                                type="search"
                                autocomplete="off"
                                v-model="searchQuery"
                                @focus="onFocus"
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
                                    <div v-if="selectedOption.icon || icon" class="size-4">
                                        <Icon :name="selectedOption.icon ?? icon" class="text-gray-900 dark:text-white dark:opacity-50" />
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
                        :data-ui-exclude-z-manipulation="excludeZManipulation ? '' : undefined"
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
                                        <span v-if="labelHtml" v-html="getOptionLabel(option)" />
                                        <template v-else>{{ getOptionLabel(option) }}</template>
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
                                    :text-content="getOptionPlainLabel"
                                    v-slot="{ option }"
                                >
                                    <div class="py-1 px-2 w-full overflow-x-hidden">
                                        <ComboboxItem
                                            as="button"
                                            :key="`${getOptionValue(option)}-${isDisabled(option)}`"
                                            :value="getOptionValue(option)"
                                            :text-value="getOptionPlainLabel(option)"
                                            :disabled="isDisabled(option)"
                                            :class="itemClasses({ size: size, selected: isSelected(option) })"
                                            :data-ui-combobox-item="getOptionValue(option)"
                                            :title="getOptionPlainLabel(option)"
                                            @select="select(option)"
                                        >
                                            <slot name="option" v-bind="option">
                                                <span v-if="option.create" class="truncate">{{ __('Add ":value"', { value: getOptionLabel(option) }) }}</span>
                                                <template v-else>
                                                    <img v-if="option.image" :src="option.image" class="size-5 rounded-full" :alt="getOptionPlainLabel(option)">
                                                    <Icon v-else-if="option.icon" :name="option.icon" />
                                                    <span v-if="labelHtml" class="truncate" v-html="getOptionLabel(option)" />
                                                    <span class="truncate" v-else>{{ __(getOptionLabel(option)) }}</span>
                                                </template>
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

        <slot name="selected-options" v-bind="{ disabled, readOnly, getOptionLabel, getOptionPlainLabel, getOptionValue, labelHtml, deselect }">
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
                <div class="flex flex-wrap gap-2" :class="{ 'mt-2.5': selectedOptions.length }">
                    <div
                        v-for="option in selectedOptions"
                        :key="getOptionValue(option)"
                        class="sortable-item cursor-grab active:cursor-grabbing"
                    >
                        <Badge pill size="lg" class="[&>*:not(svg)]:st-text-trim-optical">
                            <Icon v-if="option.icon" :name="option.icon" />
                            <div v-if="labelHtml" v-html="getOptionLabel(option)"></div>
                            <div v-else>{{ __(getOptionLabel(option)) }}</div>

                            <button
                                v-if="!disabled && !readOnly"
                                type="button"
                                class="opacity-75 hover:opacity-100 cursor-pointer"
                                :aria-label="__('Remove :label', { label: getOptionPlainLabel(option) })"
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

