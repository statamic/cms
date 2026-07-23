<script setup>
import { computed, ref, watch } from 'vue';
import { SortableList } from '@/components/sortable/Sortable.js';
import { __ } from '@/bootstrap/globals.js';
import DragHandle from './DragHandle.vue';

const props = defineProps({
    /** Whether the list can be reordered. */
    disabled: { type: Boolean, default: false },
    /** The controlled order, as an array of option values. */
    modelValue: { type: Array, default: () => [] },
    /** Name attribute for the hidden inputs holding the order. */
    name: { type: String, default: null },
    /** The items to rank. Each option supports `value` and `label`. */
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const sortableItemClass = 'rank-list-item';
const sortableHandleClass = 'rank-list-handle';

const ranked = ref([]);

const defaultOrder = computed(() => props.options.map((option) => option.value));

const rows = computed(() =>
    ranked.value.map((value, index) => ({
        value,
        label: props.options.find((option) => option.value === value)?.label ?? value,
        index,
    })),
);

const hasCustomOrder = computed(() => JSON.stringify(ranked.value) !== JSON.stringify(defaultOrder.value));

function reconcile(order) {
    const values = new Set(defaultOrder.value);
    const reconciled = (Array.isArray(order) && order.length ? order : defaultOrder.value).filter((value) =>
        values.has(value),
    );

    defaultOrder.value.forEach((value) => {
        if (!reconciled.includes(value)) {
            reconciled.push(value);
        }
    });

    return reconciled;
}

function moveToRank(value, event) {
    const fromIndex = ranked.value.indexOf(value);
    const requested = Number(event.target.value);

    if (!props.disabled && fromIndex !== -1 && Number.isFinite(requested)) {
        const toIndex = Math.max(0, Math.min(ranked.value.length - 1, Math.round(requested) - 1));

        if (toIndex !== fromIndex) {
            const next = [...ranked.value];
            next.splice(fromIndex, 1);
            next.splice(toIndex, 0, value);
            ranked.value = next;
        }
    }

    // Reset the input to the item's canonical rank. When the requested rank is
    // clamped to the item's current position, no reorder happens, so the binding
    // wouldn't otherwise overwrite the invalid value left in the input.
    event.target.value = ranked.value.indexOf(value) + 1;
}

watch([() => props.modelValue, () => props.options], () => (ranked.value = reconcile(props.modelValue)), {
    deep: true,
    immediate: true,
});

watch(
    ranked,
    (value) => {
        if (JSON.stringify(value) === JSON.stringify(props.modelValue)) return;

        emit('update:modelValue', [...value]);
    },
    { deep: true },
);
</script>

<template>
    <SortableList
        v-if="rows.length"
        v-model="ranked"
        :vertical="true"
        :item-class="sortableItemClass"
        :handle-class="sortableHandleClass"
        :mirror="false"
        :disabled="disabled"
    >
        <ul class="flex flex-col gap-2" role="list" data-ui-rank-list>
            <li
                v-for="row in rows"
                :key="row.value"
                :class="sortableItemClass"
                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 dark:border-gray-700 dark:bg-gray-900"
            >
                <DragHandle
                    v-if="!disabled"
                    :class="[
                        sortableHandleClass,
                        hasCustomOrder ? 'text-primary! [&_svg]:opacity-100' : '[&_svg]:opacity-75 dark:[&_svg]:opacity-50',
                    ]"
                    class="cursor-grab"
                />
                <input
                    v-if="!disabled"
                    type="number"
                    class="size-6 shrink-0 rounded-md border border-gray-300 bg-white px-0 text-center text-xs font-semibold text-gray-800 shadow-ui-xs focus:focus-outline dark:border-gray-700 dark:bg-gray-925 dark:text-gray-200 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    :min="1"
                    :max="rows.length"
                    step="1"
                    :value="row.index + 1"
                    :aria-label="`${__('Rank')} ${row.label}`"
                    @change="moveToRank(row.value, $event)"
                />
                <span
                    v-else
                    class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-semibold text-gray-800 shadow-ui-xs dark:border-gray-700 dark:bg-gray-925 dark:text-gray-200"
                    aria-hidden="true"
                >
                    {{ row.index + 1 }}
                </span>
                <span class="min-w-0 flex-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ row.label }}
                </span>
                <input v-if="name" type="hidden" :name="`${name}[]`" :value="row.value" />
            </li>
        </ul>
    </SortableList>
</template>

<style scoped>
.rank-list-item.draggable-source--is-dragging {
    /* Make the item slightly more transparent than the default dragging opacity */
    opacity: 0.65;
}
</style>
