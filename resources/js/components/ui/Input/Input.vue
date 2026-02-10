<script setup>
import { computed, useSlots, useAttrs, ref, useId, useTemplateRef, onMounted, nextTick } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import Icon from '../Icon/Icon.vue';
import Button from '../Button/Button.vue';
import CharacterCounter from '../CharacterCounter.vue';
import useCopy from '@/composables/copy';

defineOptions({ inheritAttrs: false });

const slots = useSlots();
const attrs = useAttrs();

const props = defineProps({
    /** Appended text */
    append: { type: String, default: null },
    /** Badge text to display on the right side */
    badge: { type: String, default: null },
    /** When `true`, shows a clear button to empty the input */
    clearable: { type: Boolean, default: false },
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: String, default: null },
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconAppend: { type: String, default: null },
    /** Icon name. Will display before the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconPrepend: { type: String, default: null },
    /** ID attribute for the input element */
    id: { type: String, default: () => useId() },
    /** Specify a character limit */
    limit: { type: Number, default: null },
    /** When `true`, an animated loading indicator will show next to the input */
    loading: { type: Boolean, default: false },
    /** The controlled value of the input */
    modelValue: { type: [String, Number], default: null },
    placeholder: { type: String, default: null },
    /** Prepended text */
    prepend: { type: String, default: null },
    required: { type: Boolean, default: false },
    /** Controls the size of the input. Options: `xs`, `sm`, `base`, `lg` */
    size: { type: String, default: 'base' },
    /** Input type attribute */
    type: { type: String, default: 'text' },
    /** Controls the appearance of the input. Options: `default`, `filled` */
    variant: { type: String, default: 'default' },
    /** When `true`, shows an eye icon to toggle password visibility */
    viewable: { type: Boolean, default: false },
    /** When `true`, autofocuses the input on mount */
    focus: { type: Boolean, default: false },
    /** Additional attributes to apply to the input element */
    inputAttrs: { type: [Object, String], default: () => ({}) },
    /** Additional CSS classes for the input element */
    inputClass: { type: String, default: '' },
});

const inputAttributeKeys = [
    'accept', 'autocomplete', 'autofocus', 'capture', 'checked', 'dirname', 'form',
    'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget',
    'list', 'max', 'maxlength', 'min', 'minlength', 'multiple', 'name', 'pattern',
    'readonly', 'required', 'size', 'src', 'step', 'tabindex', 'value'
];

const outerAttrs = computed(() => {
    const result = {};
    for (const key in attrs) {
        if (!inputAttributeKeys.includes(key.toLowerCase())) result[key] = attrs[key];
    }
    return result;
});

const normalizedInputAttrs = computed(() => {
    if (typeof props.inputAttrs === 'string') {
        return props.inputAttrs
            .split(' ')
            .filter(attr => attr.length > 0)
            .reduce((acc, attr) => ({ ...acc, [attr]: true }), {});
    }
    return props.inputAttrs;
});

const inputAttrs = computed(() => {
    const result = {};
    for (const key in attrs) {
        if (inputAttributeKeys.includes(key.toLowerCase())) result[key] = attrs[key];
    }
    return { ...result, ...normalizedInputAttrs.value };
});

const hasPrependedIcon = computed(() => !!props.iconPrepend || !!props.icon || !!slots.prepend);
const hasAppendedIcon = computed(() => !!props.iconAppend || !!slots.append || clearable.value || props.viewable || canCopy.value || props.loading);

const inputClasses = computed(() => {
    const classes = cva({
        base: [
            'w-full block bg-white dark:bg-gray-900',
            'border border-gray-300 with-contrast:border-gray-500 dark:border-gray-700 dark:with-contrast:border-gray-500 dark:inset-shadow-2xs dark:inset-shadow-black',
            'text-gray-925 dark:text-gray-300 placeholder:text-gray-500 dark:placeholder:text-gray-400/85',
            'appearance-none antialiased shadow-ui-sm disabled:shadow-none disabled:opacity-50 read-only:border-dashed not-prose',
        ],
        variants: {
            size: {
                base: 'text-base rounded-lg px-3 py-2 h-10 leading-[1.375rem]',
                sm: 'text-sm rounded-md px-2.5 py-1.5 h-8 leading-[1.125rem]',
                xs: 'text-xs rounded-xs px-2 py-1.5 h-6 leading-[1.125rem]',
            },
            variant: {
                default: '',
                light: 'dark:bg-gray-800/20',
                ghost: 'bg-transparent border-none shadow-none! inset-shadow-none!',
            },
            hasLimit: {
                true: 'pe-9',
            },
        },
        compoundVariants: [
            { hasPrependedIcon: true, size: 'base', class: 'ps-9' },
            { hasPrependedIcon: true, size: 'sm', class: 'ps-8' },
            { hasPrependedIcon: true, size: 'xs', class: 'ps-6' },
            { hasAppendedIcon: true, size: 'base', class: 'pe-10' },
            { hasAppendedIcon: true, size: 'sm', class: 'pe-8' },
            { hasAppendedIcon: true, size: 'xs', class: 'pe-6' },
        ],
    })({
        ...props,
        hasPrependedIcon: hasPrependedIcon.value,
        hasAppendedIcon: hasAppendedIcon.value,
        hasLimit: !!props.limit,
    });

    return twMerge(classes, props.inputClass);
});

const iconClasses = computed(() => {
    const classes = cva({
        base: 'absolute top-0 bottom-0 flex items-center justify-center text-xs text-gray-400 dark:text-gray-500',
        variants: {
            size: {
                base: '[&_svg]:size-4',
                sm: '[&_svg]:size-3.5',
                xs: '[&_svg]:size-3',
            },
        },
    })({ ...props });

    return twMerge(classes);
});

const prependedIconClasses = computed(() => {
    const classes = cva({
        base: 'start-0',
        variants: {
            size: {
                base: 'ps-3 has-[button]:ps-1',
                sm: 'ps-2 has-[button]:ps-1',
                xs: 'ps-1.5 has-[button]:ps-0',
            },
        },
    })({ ...props });

    return twMerge(iconClasses.value, classes);
});

const appendedIconClasses = computed(() => {
    const classes = cva({
        base: 'end-0',
        variants: {
            size: {
                base: 'pe-3 has-[button]:pe-1',
                sm: 'pe-2 has-[button]:pe-1',
                xs: 'pe-1.5 has-[button]:pe-0',
            },
        },
    })({ ...props });

    return twMerge(iconClasses.value, classes);
});

const emit = defineEmits(['update:modelValue']);
const clear = () => {
    emit('update:modelValue', '');
};

const inputType = ref(props.type);
const togglePassword = () => {
    inputType.value = inputType.value === 'password' ? 'text' : 'password';
};

const { copySupported, copied, copy } = useCopy();
const canCopy = computed(() => props.copyable && copySupported.value);

const clearable = computed(() => props.clearable && !props.readOnly && !props.disabled && !!props.modelValue);

const input = useTemplateRef('input');
const focus = () => input.value.focus();

onMounted(() => {
    if (props.focus) {
        nextTick(() => focus());
    }
})

defineExpose({ focus });
</script>

<template>
    <ui-input-group v-bind="outerAttrs">
        <ui-input-group-prepend v-if="prepend" v-text="prepend" />
        <div class="group/input relative block w-full st-text-legibility" data-ui-input>
            <div v-if="hasPrependedIcon" :class="prependedIconClasses">
                <slot name="prepend">
                    <Icon :name="iconPrepend || icon" />
                </slot>
            </div>
            <input
                ref="input"
                :class="inputClasses"
                :id
                :type="inputType"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readOnly"
                data-ui-control
                data-ui-group-target
                v-bind="inputAttrs"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <div v-if="hasAppendedIcon" :class="appendedIconClasses">
                <slot name="append">
                    <Button size="sm" icon="x" variant="ghost" v-if="clearable" @click="clear" />
                    <Button
                        size="sm"
                        :icon="inputType === 'password' ? 'eye' : 'eye-closed'"
                        variant="ghost"
                        v-else-if="viewable"
                        @click="togglePassword"
                    />
                    <Button
                        size="sm"
                        :icon="copied ? 'clipboard-check' : 'clipboard'"
                        variant="subtle"
                        v-else-if="canCopy"
                        @click="copy(modelValue)"
                        class="animate"
                        :class="copied ? 'animate-wiggle' : ''"
                    />
                    <Icon v-else-if="iconAppend" :name="iconAppend" />
                    <Icon v-if="loading" name="loading" />
                </slot>
            </div>
            <div v-if="limit" class="absolute inset-y-0 right-2 flex items-center">
                <CharacterCounter :text="modelValue" :limit />
            </div>
        </div>
        <ui-input-group-append v-if="append" v-text="append" />
    </ui-input-group>
</template>
