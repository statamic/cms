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

const gridStyle = computed(() => ({
    '--image-choice-columns': columns.value,
}));

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

function normalizeOptions(raw) {
    if (! raw) {
        return [];
    }

    if (Array.isArray(raw)) {
        return raw
            .filter((row) => row && (row.key || row.value))
            .map((row) => ({
                value: row.key ?? row.value,
                label: row.label ?? row.key ?? row.value,
                image: resolveImage(row.image),
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

</script>

<template>
    <div
        class="image-choice"
        :class="{ 'image-choice--multiple': multiple }"
        :style="gridStyle"
        :role="multiple ? 'group' : 'radiogroup'"
    >
        <label
            v-for="option in options"
            :key="option.value"
            class="image-choice__option"
            :class="{ 'image-choice__option--selected': isSelected(option.value) }"
        >
            <input
                class="image-choice__input"
                :type="multiple ? 'checkbox' : 'radio'"
                :name="multiple ? `${name}[]` : name"
                :value="option.value"
                :checked="isSelected(option.value)"
                :disabled="isDisabled"
                @change="toggleOption(option.value)"
            >
            <span class="image-choice__card">
                <span class="image-choice__media">
                    <img
                        v-if="option.image"
                        class="image-choice__image"
                        :src="option.image"
                        :alt="option.label"
                    >
                    <span v-else class="image-choice__placeholder" aria-hidden="true">
                        <ui-icon name="image" class="size-6 text-gray-400" />
                    </span>
                </span>
                <span v-if="option.label" class="image-choice__label">{{ __(option.label) }}</span>
            </span>
        </label>
    </div>
</template>
