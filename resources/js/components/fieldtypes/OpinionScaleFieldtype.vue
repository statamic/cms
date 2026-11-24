<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const optionClasses = [
    'relative flex size-9 shrink-0 cursor-pointer items-center justify-center',
    'rounded-md border shadow-ui-xs text-xs font-medium',
    'border-gray-300 bg-white text-gray-800 hover:bg-gray-50',
    'dark:border-gray-700 dark:bg-gray-925 dark:text-gray-200 dark:hover:bg-gray-925',
    'peer-checked:border-primary peer-checked:ring-[1px] peer-checked:ring-primary',
    'peer-focus-visible:focus-outline peer-focus-visible:transition-none',
];

const min = computed(() => props.config.min ?? 0);
const max = computed(() => props.config.max ?? 10);
const isDisabled = computed(() => props.config.disabled || isReadOnly.value);
const ariaLabel = computed(() => __(props.config.display ?? 'Opinion scale'));
const hasLabels = computed(() => Boolean(props.config.low_label || props.config.middle_label || props.config.high_label));
</script>

<template>
    <div class="inline-flex w-fit max-w-full flex-col gap-2" data-opinion-scale>
        <div
            class="flex gap-2"
            role="radiogroup"
            :aria-label="ariaLabel"
        >
            <label
                v-for="scaleValue in meta.scaleValues"
                :key="scaleValue"
                class="cursor-pointer has-disabled:cursor-not-allowed has-disabled:opacity-60"
            >
                <input
                    type="radio"
                    class="peer sr-only"
                    :name="name"
                    :value="scaleValue"
                    :checked="value === scaleValue"
                    :disabled="isDisabled"
                    @change="update(scaleValue)"
                >
                <span :class="optionClasses">
                    <span class="leading-none">{{ scaleValue }}</span>
                </span>
            </label>
        </div>

        <div
            v-if="hasLabels"
            class="grid w-full grid-cols-[1fr_auto_1fr] gap-2 px-0.75 text-xs text-gray-500 dark:text-gray-400"
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
