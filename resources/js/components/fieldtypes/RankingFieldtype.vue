<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { SortableList } from '@/components/sortable/Sortable.js';
import { DragHandle } from '@/components/ui';
import { computed, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const sortableItemClass = 'ranking-item';
const sortableHandleClass = 'ranking-handle';

const rankedValues = ref([]);

const options = computed(() => {
    const raw = props.config.options ?? {};
    return Object.entries(raw).map(([value, label]) => ({ value, label: label || value }));
});

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

const hasCustomOrder = computed(() => {
    return JSON.stringify(rankedValues.value) !== JSON.stringify(defaultOrder());
});

function moveToRank(itemValue, event) {
    const fromIndex = rankedValues.value.indexOf(itemValue);
    const parsed = Number(event.target.value);

    if (! isDisabled.value && fromIndex !== -1 && Number.isFinite(parsed)) {
        const toIndex = Math.max(0, Math.min(rankedValues.value.length - 1, Math.round(parsed) - 1));

        if (toIndex !== fromIndex) {
            const next = [...rankedValues.value];
            next.splice(fromIndex, 1);
            next.splice(toIndex, 0, itemValue);
            rankedValues.value = next;
        }
    }

    // Reset the input to the item's canonical rank. When the requested rank is
    // clamped to the item's current position, no reorder happens, so the binding
    // wouldn't otherwise overwrite the invalid value left in the input.
    event.target.value = rankedValues.value.indexOf(itemValue) + 1;
}

watch(
    [() => props.value, options],
    () => rankedValues.value = buildRankedValues(props.value),
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
                <DragHandle
                    v-if="!isDisabled"
                    :class="[
                        sortableHandleClass,
                        hasCustomOrder
                            ? 'text-primary! [&_svg]:opacity-100'
                            : '[&_svg]:opacity-75 dark:[&_svg]:opacity-50',
                    ]"
                    class="cursor-grab"
                />
                <input
                    v-if="!isDisabled"
                    type="number"
                    class="size-6 shrink-0 rounded-md border border-gray-300 bg-white px-0 text-center text-xs font-semibold text-gray-800 shadow-ui-xs focus:focus-outline dark:border-gray-700 dark:bg-gray-925 dark:text-gray-200 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    :min="1"
                    :max="orderedRows.length"
                    step="1"
                    :value="row.index + 1"
                    :aria-label="`${__('Rank')} ${__(row.label)}`"
                    @change="moveToRank(row.value, $event)"
                >
                <span
                    v-else
                    class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 shadow-ui-xs bg-white text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-925 dark:text-gray-200"
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

<style scoped>
.ranking-item.draggable-source--is-dragging {
    /* Make the item slightly transparent than the default dragging opacity */
    opacity: 0.65;
}
</style>
