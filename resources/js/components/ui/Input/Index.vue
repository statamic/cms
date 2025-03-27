<script setup>
import { computed, useSlots, ref, useId } from 'vue';
import { cva } from 'cva';
import { twMerge } from 'tailwind-merge';

const slots = useSlots();

const props = defineProps({
    badge: { type: String, default: null },
    clearable: { type: Boolean, default: false },
    copyable: { type: Boolean, default: false },
    description: { type: String, default: null },
    icon: { type: String, default: null },
    iconAppend: { type: String, default: null },
    iconPrepend: { type: String, default: null },
    id: { type: String, default: () => useId() },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: [String, Number], default: null },
    size: { type: String, default: 'base' },
    type: { type: String, default: 'text' },
    viewable: { type: Boolean, default: false },
    prepend: { type: String, default: null },
    append: { type: String, default: null },
});

const hasPrependedIcon = !!props.iconPrepend || !!props.icon || !!slots.prepend;
const hasAppendedIcon = !!props.iconAppend || !!slots.append || props.clearable || props.viewable || props.copyable;

const inputClasses = computed(() => {
    const classes = cva({
        base: [
            'w-full block bg-white dark:bg-gray-900',
            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
            'text-gray-600 dark:text-gray-300',
            'appearance-none antialiased shadow-ui-sm disabled:shadow-none not-prose',
        ],
        variants: {
            size: {
                base: 'text-base rounded-lg px-3 py-2 h-10 leading-[1.375rem]',
                sm: 'text-sm rounded-md px-2.5 py-1.5 h-8 leading-[1.125rem]',
                xs: 'text-xs rounded-xs px-2 py-1.5 h-6 leading-[1.125rem]',
            },
        },
        compoundVariants: [
            { hasPrependedIcon: true, size: 'base', class: 'ps-10' },
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
    });

    return twMerge(classes);
});

const iconClasses = cva({
    base: 'absolute top-0 bottom-0 flex items-center justify-center text-xs text-gray-400/75',
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
</script>

<template>
    <ui-with-field :label :description :required :badge>
        <div class="group/input relative block w-full" data-ui-input>
            <div v-if="hasPrependedIcon" :class="iconClasses">
                <slot name="prepend">
                    <ui-icon :name="iconPrepend || icon" />
                </slot>
            </div>
            <input
                :class="inputClasses"
                :id
                :type="inputType"
                :value="modelValue"
                data-ui-control
                data-ui-group-target
                v-bind="$attrs"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <div v-if="hasAppendedIcon" :class="iconClasses">
                <slot name="append">
                    <ui-button size="sm" icon="x" variant="ghost" v-if="clearable" @click="clear" />
                    <ui-button
                        size="sm"
                        :icon="inputType === 'password' ? 'eye' : 'eye-closed'"
                        variant="ghost"
                        v-else-if="viewable"
                        @click="togglePassword"
                    />
                    <ui-button
                        size="sm"
                        :icon="copied ? 'clipboard-check' : 'clipboard'"
                        variant="ghost"
                        v-else-if="copyable"
                        @click="copy"
                        class="animate"
                        :class="copied ? 'animate-wiggle' : ''"
                    />
                    <ui-icon v-else :name="iconAppend" />
                </slot>
            </div>
        </div>
    </ui-with-field>
</template>
