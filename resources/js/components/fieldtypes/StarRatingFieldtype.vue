<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed, useTemplateRef } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview, name } = Fieldtype.use(emit, props);

const rangeInput = useTemplateRef('rangeInput');

defineExpose({
    ...expose,
    focus: () => rangeInput.value?.focus(),
});

const maxStars = computed(() => {
    const max = Number(props.config.max_stars);

    if (! Number.isFinite(max)) {
        return 5;
    }

    return Math.max(1, Math.min(Math.round(max), 10));
});

const allowHalfStars = computed(() => Boolean(props.config.allow_half_stars));

const step = computed(() => (allowHalfStars.value ? 0.5 : 1));

const min = computed(() => 0);

const normalizedValue = computed(() => {
    if (props.value == null || props.value === '') {
        return null;
    }

    const value = Number(props.value);

    if (! Number.isFinite(value)) {
        return null;
    }

    if (! allowHalfStars.value) {
        return Math.round(value);
    }

    return Math.round(value * 2) / 2;
});

const rangeValue = computed(() => normalizedValue.value ?? 0);

const isUnrated = computed(() => normalizedValue.value == null || normalizedValue.value === 0);

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const rangeStyle = computed(() => ({
    '--star-rating-max': maxStars.value,
    '--star-rating-step': step.value,
}));

const ariaLabel = computed(() => {
    if (props.config.display) {
        return __(props.config.display);
    }

    return __('Star rating');
});

const onInput = (event) => {
    update(Number(event.target.value));
};

defineReplicatorPreview(() => {
    if (normalizedValue.value == null) {
        return null;
    }

    return `${normalizedValue.value}/${maxStars.value}`;
});

</script>

<template>
    <input
        ref="rangeInput"
        type="range"
        class="star-rating-input"
        :class="{ 'star-rating-input--unrated': isUnrated }"
        data-star-rating
        :name="name"
        :min="min"
        :max="maxStars"
        :step="step"
        :value="rangeValue"
        :disabled="isDisabled"
        :aria-label="ariaLabel"
        :style="rangeStyle"
        @input="onInput"
    />
</template>
