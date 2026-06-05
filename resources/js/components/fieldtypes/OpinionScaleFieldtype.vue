<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview, name } = Fieldtype.use(emit, props);

const optionClass = 'relative flex min-w-10 shrink-0 cursor-pointer items-center justify-center border border-gray-300 -ms-px bg-white px-3 py-2 text-center text-sm font-semibold text-gray-800 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-925';

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
    props.config.low_label || props.config.middle_label || props.config.high_label,
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
    <div class="inline-flex w-fit max-w-full flex-col gap-2" data-opinion-scale>
        <div
            class="flex overflow-x-auto"
            role="radiogroup"
            :aria-label="ariaLabel"
        >
            <label
                v-for="(value, index) in scaleValues"
                :key="value"
                :class="[
                    optionClass,
                    {
                        'z-1 ms-0 rounded-s-lg border-primary bg-primary/10 text-primary dark:bg-primary/20': selectedValue === value,
                        'rounded-s-lg ms-0': index === 0 && selectedValue !== value,
                        'rounded-e-lg': index === scaleValues.length - 1,
                    },
                ]"
            >
                <input
                    type="radio"
                    class="absolute pointer-events-none opacity-0"
                    :name="name"
                    :value="value"
                    :checked="selectedValue === value"
                    :disabled="isDisabled"
                    @change="selectValue(value)"
                >
                <span class="leading-none">{{ value }}</span>
            </label>
        </div>

        <div
            v-if="hasLabels"
            class="grid w-full grid-cols-[1fr_auto_1fr] gap-2 text-xs text-gray-500 dark:text-gray-400"
        >
            <span v-if="config.low_label" class="col-start-1 justify-self-start text-start">
                {{ __(config.low_label) }}
            </span>
            <span v-if="config.middle_label" class="col-start-2 justify-self-center text-center">
                {{ __(config.middle_label) }}
            </span>
            <span v-if="config.high_label" class="col-start-3 justify-self-end text-end">
                {{ __(config.high_label) }}
            </span>
        </div>
    </div>
</template>
