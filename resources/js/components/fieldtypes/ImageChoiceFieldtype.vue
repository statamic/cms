<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Icon } from '@/components/ui';
import { computed, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const multiple = computed(() => Boolean(props.config.multiple));
const isDisabled = computed(() => props.config.disabled || isReadOnly.value);
const options = computed(() => props.meta?.options ?? []);
const columns = computed(() => props.config.columns ?? 3);
const aspectRatio = computed(() => props.config.aspect_ratio ?? '16/9');
const gap = computed(() => props.config.gap ?? 3);

const selectedValues = ref(props.value);

const cardClasses = [
    'flex flex-col gap-2 h-full p-1 pb-2 rounded-lg border shadow-ui-xs transition-colors',
    'border-gray-300 bg-white',
    'dark:border-gray-700 dark:bg-gray-900',
    'hover:border-gray-400 dark:hover:border-gray-700',
    'peer-checked:border-primary peer-checked:ring-1 peer-checked:ring-primary',
    'peer-focus-visible:border-primary peer-focus-visible:ring-1 peer-focus-visible:ring-primary',
];

const gridClass = computed(() => ({
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
})[columns.value] ?? 'grid-cols-3');

const gapClass = computed(() => ({
    2: 'gap-2',
    3: 'gap-3',
    4: 'gap-4',
})[gap.value] ?? 'gap-3');

function isSelected(key) {
    return selectedValues.value.includes(key);
}

function toggleOption(key) {
    if (isDisabled.value) return;

    if (isSelected(key)) {
        selectedValues.value = selectedValues.value.filter((v) => v !== key);
    } else if (multiple.value) {
        selectedValues.value = [...selectedValues.value, key];
    } else {
        selectedValues.value = [key];
    }

    update(selectedValues.value);
}

watch(() => props.value, (value) => selectedValues.value = value);
</script>

<template>
    <div
        class="grid"
        :class="[gapClass, gridClass]"
        :role="multiple ? 'group' : 'radiogroup'"
    >
        <label
            v-for="option in options"
            :key="option.key"
            class="group cursor-pointer has-disabled:cursor-not-allowed has-disabled:opacity-60"
        >
            <input
                class="peer sr-only"
                :type="multiple ? 'checkbox' : 'radio'"
                :name="multiple ? `${name}[]` : name"
                :value="option.key"
                :checked="isSelected(option.key)"
                :disabled="isDisabled"
                @change="toggleOption(option.key)"
            >
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
                    >
                    <span v-else class="flex size-full items-center justify-center" aria-hidden="true">
                        <Icon name="image" class="size-6 text-gray-400" />
                    </span>
                </span>
                <span
                    v-if="option.label"
                    class="flex items-center justify-center gap-2"
                >
                    <span
                        v-if="option.letter"
                        class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
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
