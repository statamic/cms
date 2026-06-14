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

const step = computed(() => props.config.step ?? (allowHalfStars.value ? 0.5 : 1));

const min = computed(() => props.config.min ?? (allowHalfStars.value ? 0.5 : 1));

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

const rangeValue = computed(() => {
    if (normalizedValue.value == null || normalizedValue.value === 0) {
        return min.value;
    }

    return normalizedValue.value;
});

const isUnrated = computed(() => normalizedValue.value == null || normalizedValue.value === 0);

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const ariaLabel = computed(() => __(props.config.display ?? 'Star rating'));

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
        :style="{
            '--star-rating-max': maxStars,
            '--star-rating-step': step,
        }"
        @input="update(Number($event.target.value))"
    />
</template>
