<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview, name } = Fieldtype.use(emit, props);

const min = computed(() => {
    const value = Number(props.config.min);

    return value === 1 ? 1 : 0;
});

const max = computed(() => {
    const value = Number(props.config.max);

    if (! Number.isFinite(value)) {
        return 10;
    }

    return Math.max(min.value + 1, Math.min(min.value + 10, Math.round(value)));
});

const scaleValues = computed(() => {
    const values = [];

    for (let value = min.value; value <= max.value; value++) {
        values.push(value);
    }

    return values;
});

const selectedValue = computed(() => {
    if (props.value == null || props.value === '') {
        return null;
    }

    const value = Number(props.value);

    if (! Number.isFinite(value)) {
        return null;
    }

    if (value < min.value || value > max.value) {
        return null;
    }

    return value;
});

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const hasLabels = computed(() => Boolean(
    props.config.left_label || props.config.center_label || props.config.right_label,
));

const ariaLabel = computed(() => props.config.display ? __(props.config.display) : __('Opinion scale'));

function selectValue(value) {
    if (isDisabled.value) {
        return;
    }

    update(value);
}

defineReplicatorPreview(() => {
    if (selectedValue.value == null) {
        return null;
    }

    return `${selectedValue.value}/${max.value}`;
});

defineExpose({
    ...expose,
});
</script>

<template>
    <div class="opinion-scale" data-opinion-scale>
        <div
            class="opinion-scale__options"
            role="radiogroup"
            :aria-label="ariaLabel"
        >
            <label
                v-for="(value, index) in scaleValues"
                :key="value"
                class="opinion-scale__option"
                :class="{
                    'opinion-scale__option--selected': selectedValue === value,
                    'opinion-scale__option--first': index === 0,
                    'opinion-scale__option--last': index === scaleValues.length - 1,
                }"
            >
                <input
                    type="radio"
                    class="opinion-scale__input"
                    :name="name"
                    :value="value"
                    :checked="selectedValue === value"
                    :disabled="isDisabled"
                    @change="selectValue(value)"
                >
                <span class="opinion-scale__value">{{ value }}</span>
            </label>
        </div>

        <div v-if="hasLabels" class="opinion-scale__labels">
            <span v-if="config.left_label" class="opinion-scale__label opinion-scale__label--left">
                {{ __(config.left_label) }}
            </span>
            <span v-if="config.center_label" class="opinion-scale__label opinion-scale__label--center">
                {{ __(config.center_label) }}
            </span>
            <span v-if="config.right_label" class="opinion-scale__label opinion-scale__label--right">
                {{ __(config.right_label) }}
            </span>
        </div>
    </div>
</template>
