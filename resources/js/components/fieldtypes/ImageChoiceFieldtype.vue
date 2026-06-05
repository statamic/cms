<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview, name } = Fieldtype.use(emit, props);

const multiple = computed(() => Boolean(props.config.multiple));

const selectedValues = ref(normalizeSelected(props.value));

const options = computed(() => normalizeOptions(props.meta?.options ?? props.config.image_options ?? props.config.options));

const columns = computed(() => {
    const cols = Number(props.config.columns);

    if (! Number.isFinite(cols)) {
        return 3;
    }

    return Math.max(1, Math.min(Math.round(cols), 4));
});

const gridClass = computed(() => {
    return {
        1: 'grid-cols-1',
        2: 'grid-cols-2',
        3: 'grid-cols-3',
        4: 'grid-cols-4',
    }[columns.value] ?? 'grid-cols-3';
});

const gapClass = computed(() => {
    const gap = Number(props.config.gap);

    return {
        2: 'gap-2',
        3: 'gap-3',
        4: 'gap-4',
    }[Number.isFinite(gap) ? gap : 3] ?? 'gap-3';
});

const aspectRatio = computed(() => {
    const allowed = ['1/1', '4/3', '3/2', '16/9', '3/4', '2/3'];
    const ratio = props.config.aspect_ratio ?? '16/9';

    return allowed.includes(ratio) ? ratio : '16/9';
});

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

function normalizeOptions(raw) {
    if (! raw) {
        return [];
    }

    if (Array.isArray(raw)) {
        return raw
            .filter((row) => row && (row.key || row.value))
            .map((row, index) => ({
                value: row.key ?? row.value,
                label: row.label ?? row.key ?? row.value,
                image: resolveImage(row.image),
                letter: row.letter ?? String.fromCharCode(65 + index),
            }));
    }

    return Object.entries(raw).map(([value, label]) => ({
        value,
        label: typeof label === 'string' ? label : value,
        image: null,
    }));
}

function resolveImage(image) {
    if (! image) {
        return null;
    }

    if (Array.isArray(image)) {
        image = image[0];
    }

    if (typeof image !== 'string') {
        return null;
    }

    if (image.startsWith('http') || image.startsWith('/')) {
        return image;
    }

    return image;
}

function normalizeSelected(value) {
    if (multiple.value) {
        return Array.isArray(value) ? [...value] : [];
    }

    if (value == null || value === '') {
        return null;
    }

    return value;
}

function isSelected(value) {
    if (multiple.value) {
        return selectedValues.value.includes(value);
    }

    return selectedValues.value === value;
}

function toggleOption(value) {
    if (isDisabled.value) {
        return;
    }

    if (multiple.value) {
        const values = [...selectedValues.value];

        if (values.includes(value)) {
            selectedValues.value = values.filter((item) => item !== value);
        } else {
            selectedValues.value = [...values, value];
        }

        update(selectedValues.value);

        return;
    }

    selectedValues.value = value;
    update(value);
}

watch(
    () => props.value,
    (value) => {
        selectedValues.value = normalizeSelected(value);
    },
);

defineReplicatorPreview(() => {
    if (multiple.value) {
        if (! selectedValues.value?.length) {
            return null;
        }

        return selectedValues.value
            .map((value) => options.value.find((option) => option.value === value)?.label ?? value)
            .join(', ');
    }

    if (selectedValues.value == null || selectedValues.value === '') {
        return null;
    }

    const option = options.value.find((item) => item.value === selectedValues.value);

    return option?.label ?? selectedValues.value;
});

defineExpose({
    ...expose,
});

const cardClasses = [
    'flex flex-col gap-2 h-full p-1 pb-2 rounded-lg border shadow-ui-xs transition-colors',
    'border-gray-300 bg-white',
    'dark:border-gray-700 dark:bg-gray-900',
    'hover:border-gray-400 dark:hover:border-gray-700',
    'peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary',
    'peer-focus-visible:border-primary peer-focus-visible:ring-1 peer-focus-visible:ring-primary',
];

</script>

<template>
    <div
        class="grid"
        :class="[gapClass, gridClass]"
        :role="multiple ? 'group' : 'radiogroup'"
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
                :disabled="isDisabled"
                @change="toggleOption(option.value)"
            >
            <span :class="cardClasses">
                <span
                    class="flex items-center justify-center overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800"
                    :style="{ aspectRatio: aspectRatio }"
                >
                    <img
                        v-if="option.image"
                        class="block size-full object-cover"
                        :src="option.image"
                        :alt="option.label"
                    >
                    <span v-else class="flex size-full items-center justify-center" aria-hidden="true">
                        <ui-icon name="image" class="size-6 text-gray-400" />
                    </span>
                </span>
                <span
                    v-if="option.label"
                    class="flex items-center justify-center gap-2"
                >
                    <span
                        v-if="option.letter"
                        class="flex size-6.5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    >
                        {{ option.letter }}
                    </span>
                    <span class="text-xs text-gray-800 dark:text-gray-200" style="text-box-trim: trim-start;">
                        {{ __(option.label) }}
                    </span>
                </span>
            </span>
        </label>
    </div>
</template>
