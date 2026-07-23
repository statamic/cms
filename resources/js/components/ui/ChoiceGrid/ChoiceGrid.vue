<script setup>
import { computed, useId } from 'vue';
import { cva } from 'cva';
import Icon from '../Icon/Icon.vue';

const props = defineProps({
    /** The shape of each option's image area, e.g. `16/9`. */
    aspectRatio: { type: String, default: '16/9' },
    /** How many options appear per row. */
    columns: { type: Number, default: 3 },
    /** Whether the choices are disabled. */
    disabled: { type: Boolean, default: false },
    /** Controls the spacing between options. <br><br> Options: `sm`, `base`, `lg` */
    gap: {
        type: String,
        default: 'base',
        validator: (value) => ['sm', 'base', 'lg'].includes(value),
    },
    /** Accessible label for the group of choices. */
    label: { type: String, default: null },
    /** The controlled value. An array when `multiple`, otherwise a single value. */
    modelValue: { type: [String, Number, Array], default: null },
    /** Whether more than one option can be selected. */
    multiple: { type: Boolean, default: false },
    /** Name attribute for the choices. */
    name: { type: String, default: () => useId() },
    /** The selectable options. Each option supports `value`, `label`, `image` and `badge`. */
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const gridClasses = cva({
    base: 'grid',
    variants: {
        gap: {
            sm: 'gap-2',
            base: 'gap-3',
            lg: 'gap-4',
        },
    },
})({ ...props });

const cardClasses = [
    'flex flex-col gap-2 h-full p-1 pb-2 rounded-lg border shadow-ui-xs',
    'border-gray-300 bg-white',
    'dark:border-gray-700 dark:bg-gray-900',
    'hover:border-gray-400 dark:hover:border-gray-700',
    'peer-checked:border-primary peer-checked:ring-[1px] peer-checked:ring-primary',
    'peer-focus-visible:focus-outline peer-focus-visible:transition-none',
];

const selected = computed(() => {
    if (props.modelValue == null) return [];

    return Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue];
});

function isSelected(value) {
    return selected.value.includes(value);
}

function select(value) {
    if (props.disabled) return;

    if (!props.multiple) {
        emit('update:modelValue', value);

        return;
    }

    emit(
        'update:modelValue',
        isSelected(value) ? selected.value.filter((item) => item !== value) : [...selected.value, value],
    );
}
</script>

<template>
    <div
        :class="gridClasses"
        :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }"
        :role="multiple ? 'group' : 'radiogroup'"
        :aria-label="label"
        data-ui-choice-grid
    >
        <label
            v-for="option in options"
            :key="option.value"
            class="group cursor-pointer has-disabled:cursor-not-allowed has-disabled:opacity-60"
        >
            <input
                class="peer sr-only"
                :type="multiple ? 'checkbox' : 'radio'"
                :name="multiple ? `${name}[]` : name"
                :value="option.value"
                :checked="isSelected(option.value)"
                :disabled="disabled"
                @change="select(option.value)"
            />
            <span :class="cardClasses">
                <span
                    class="flex items-center justify-center overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800"
                    :style="{ aspectRatio }"
                >
                    <img
                        v-if="option.image"
                        class="block size-full object-cover"
                        :src="option.image"
                        :alt="option.label"
                    />
                    <span v-else class="flex size-full items-center justify-center" aria-hidden="true">
                        <Icon name="media-image-photo-focus-frame" class="size-6 text-gray-400" />
                    </span>
                </span>
                <span v-if="option.label" class="flex items-center justify-center gap-2">
                    <span
                        v-if="option.badge"
                        class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    >
                        {{ option.badge }}
                    </span>
                    <span class="text-xs text-gray-800 dark:text-gray-200" style="text-box-trim: trim-start">
                        {{ option.label }}
                    </span>
                </span>
            </span>
        </label>
    </div>
</template>
