<script setup>
import { computed, useSlots, ref, useId, useTemplateRef } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';
import { Icon, Button, CharacterCounter } from '@statamic/ui';

const slots = useSlots();

const props = defineProps({
    append: { type: String, default: null },
    badge: { type: String, default: null },
    clearable: { type: Boolean, default: false },
    copyable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
    icon: { type: String, default: null },
    iconAppend: { type: String, default: null },
    iconPrepend: { type: String, default: null },
    id: { type: String, default: () => useId() },
    limit: { type: Number, default: null },
    loading: { type: Boolean, default: false },
    modelValue: { type: [String, Number], default: null },
    placeholder: { type: String, default: null },
    prepend: { type: String, default: null },
    required: { type: Boolean, default: false },
    size: { type: String, default: 'base' },
    tabindex: { type: Number, default: null },
    type: { type: String, default: 'text' },
    viewable: { type: Boolean, default: false },
});

const hasPrependedIcon = !!props.iconPrepend || !!props.icon || !!slots.prepend;
const hasAppendedIcon = !!props.iconAppend || !!slots.append || props.clearable || props.viewable || props.copyable || props.loading;

const inputClasses = computed(() => {
    const classes = cva({
        base: [
            'w-full block bg-white dark:bg-gray-900',
            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
            'text-gray-900 dark:text-gray-300 placeholder:text-gray-400 dark:placeholder:text-gray-600',
            'appearance-none antialiased shadow-ui-sm disabled:shadow-none disabled:opacity-50 read-only:border-dashed not-prose',
        ],
        variants: {
            size: {
                base: 'text-base rounded-lg px-3 py-2 h-10 leading-[1.375rem]',
                sm: 'text-sm rounded-md px-2.5 py-1.5 h-8 leading-[1.125rem]',
                xs: 'text-xs rounded-xs px-2 py-1.5 h-6 leading-[1.125rem]',
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
        hasPrependedIcon: hasPrependedIcon,
        hasAppendedIcon: hasAppendedIcon,
        hasLimit: !!props.limit,
    });

    return twMerge(classes);
});

const iconClasses = cva({
    base: 'absolute top-0 bottom-0 flex items-center justify-center text-xs text-gray-400',
    variants: {
        size: {
            base: '[&_svg]:size-4',
            sm: '[&_svg]:size-3.5',
            xs: '[&_svg]:size-3',
        },
    },
    compoundVariants: [
        { size: 'base', hasPrependedIcon: true, class: 'ps-3 has-[button]:ps-1 start-0' },
        { size: 'sm', hasPrependedIcon: true, class: 'ps-2 has-[button]:ps-1 start-0' },
        { size: 'xs', hasPrependedIcon: true, class: 'ps-1.5 has-[button]:ps-0 start-0' },
        { size: 'base', hasAppendedIcon: true, class: 'pe-3 has-[button]:pe-1 end-0' },
        { size: 'sm', hasAppendedIcon: true, class: 'pe-2 has-[button]:pe-1 end-0' },
        { size: 'xs', hasAppendedIcon: true, class: 'pe-1.5 has-[button]:pe-0 end-0' },
    ],
})({
    ...props,
    hasPrependedIcon: hasPrependedIcon,
    hasAppendedIcon: hasAppendedIcon,
});

const emit = defineEmits(['update:modelValue']);
const clear = () => {
    emit('update:modelValue', '');
};

const inputType = ref(props.type);
const togglePassword = () => {
    inputType.value = inputType.value === 'password' ? 'text' : 'password';
};

const copied = ref(false);
const copy = () => {
    if (!props.modelValue) return;
    navigator.clipboard.writeText('props.modelValue');
    copied.value = true;
    setTimeout(() => (copied.value = false), 1000);
};

const input = useTemplateRef('input');
const focus = () => input.value.focus();

defineExpose({ focus });
</script>

<template>
    <ui-input-group>
        <ui-input-group-prepend v-if="prepend" v-text="prepend" />
        <div class="group/input relative block w-full" data-ui-input>
            <div v-if="hasPrependedIcon" :class="iconClasses">
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
                :tabindex="tabindex"
                data-ui-control
                data-ui-group-target
                v-bind="$attrs"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <div v-if="hasAppendedIcon" :class="iconClasses">
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
                        v-else-if="copyable"
                        @click="copy"
                        class="animate"
                        :class="copied ? 'animate-wiggle' : ''"
                    />
                    <Icon v-else-if="iconAppend" :name="iconAppend" />
                    <loading-graphic v-if="loading" inline text=""/>
                </slot>
            </div>
            <div v-if="limit" class="absolute inset-y-0 right-2 flex items-center">
                <CharacterCounter :text="modelValue" :limit />
            </div>
        </div>
        <ui-input-group-append v-if="append" v-text="append" />
    </ui-input-group>
</template>
