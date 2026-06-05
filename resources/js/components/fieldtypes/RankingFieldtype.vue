<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { SortableList } from '@/components/sortable/Sortable.js';
import { __ } from '@/bootstrap/globals';
import { computed, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, defineReplicatorPreview, name } = Fieldtype.use(emit, props);

const sortableItemClass = 'ranking-item';
const sortableHandleClass = 'ranking-handle';

function isCompleteOption(option) {
    const { value } = option;

    return value != null && value !== '' && value !== 'null';
}

function normalizeOptions(raw) {
    if (! raw) {
        return [];
    }

    if (Array.isArray(raw)) {
        return raw
            .filter((row) => row)
            .map((row) => {
                if (typeof row === 'object') {
                    return {
                        value: row.key ?? row.value,
                        label: row.label ?? row.value ?? row.key,
                    };
                }

                return {
                    value: row,
                    label: row,
                };
            })
            .filter(isCompleteOption);
    }

    return Object.entries(raw)
        .map(([value, label]) => ({
            value,
            label: typeof label === 'string' ? label : value,
        }))
        .filter(isCompleteOption);
}

const options = computed(() => normalizeOptions(props.meta?.options ?? props.config.options));

const rankedValues = ref([]);

const isDisabled = computed(() => props.config.disabled || isReadOnly.value);

const orderedRows = computed(() => rankedValues.value.map((value, index) => ({
    value,
    label: options.value.find((option) => option.value === value)?.label ?? value,
    index,
})));

function defaultOrder() {
    return options.value.map((option) => option.value);
}

function buildRankedValues(value) {
    const order = Array.isArray(value) && value.length ? [...value] : defaultOrder();
    const valid = new Set(options.value.map((option) => option.value));

    const ranked = order.filter((item) => valid.has(item));

    options.value.forEach((option) => {
        if (! ranked.includes(option.value)) {
            ranked.push(option.value);
        }
    });

    return ranked;
}

watch(
    [() => props.value, options],
    () => {
        rankedValues.value = buildRankedValues(props.value);
    },
    { deep: true, immediate: true },
);

watch(
    rankedValues,
    (value) => {
        const current = Array.isArray(props.value) ? props.value : [];

        if (JSON.stringify(value) === JSON.stringify(current)) {
            return;
        }

        update([...value]);
    },
    { deep: true },
);

defineReplicatorPreview(() => {
    if (! orderedRows.value.length) {
        return null;
    }

    return orderedRows.value.map((row) => row.label).join(', ');
});

defineExpose({
    ...expose,
});
</script>

<template>
    <SortableList
        v-if="orderedRows.length"
        v-model="rankedValues"
        :vertical="true"
        :item-class="sortableItemClass"
        :handle-class="sortableHandleClass"
        :mirror="false"
        :disabled="isDisabled"
    >
        <ul class="flex flex-col gap-2" role="list">
            <li
                v-for="row in orderedRows"
                :key="row.value"
                :class="sortableItemClass"
                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 dark:border-gray-700 dark:bg-gray-900"
            >
                <ui-drag-handle
                    v-if="!isDisabled"
                    :class="sortableHandleClass"
                    class="cursor-grab [&_svg]:opacity-75 dark:[&_svg]:opacity-60"
                />
                <span
                    class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 shadow-ui-xs bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    aria-hidden="true"
                >
                    {{ row.index + 1 }}
                </span>
                <span class="min-w-0 flex-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ __(row.label) }}
                </span>
                <input type="hidden" :name="`${name}[]`" :value="row.value">
            </li>
        </ul>
    </SortableList>
</template>
