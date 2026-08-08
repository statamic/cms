<script setup>
import TableField from '@/components/data-list/TableField.vue';
import RowActions from '../Listing/RowActions.vue';
import SortableList from '@/components/sortable/SortableList.vue';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import { Checkbox } from '@ui';

const props = defineProps({
    items: {
        type: Array,
    },
});

const {
    items: listingItems,
    selections,
    reorderable,
    reordered,
    visibleColumns,
    hasActions,
    allowsSelections,
    selectRange,
    selectionClicked,
    toggleSelection,
    hasReachedSelectionLimit,
    allowsMultipleSelections,
    isColumnVisible,
} = injectListingContext();

const items = computed(() => props.items ?? listingItems.value);
let lastSubsetSelectionClicked = null;

function isSelected(id) {
    return selections.value.includes(id);
}

function getCheckboxLabel(row) {
    const rowTitle = getRowTitle(row);
    return isSelected(row.id)
        ? __('Deselect :title', { title: rowTitle })
        : __('Select :title', { title: rowTitle });
}

function getCheckboxDescription(row) {
    const rowTitle = getRowTitle(row);
    const isDisabled = hasReachedSelectionLimit.value && allowsMultipleSelections.value && !isSelected(row.id);

    if (isDisabled) {
        return __('messages.selections_limit_reached', { title: rowTitle });
    }

    return isSelected(row.id)
        ? __('messages.selections_item_selected', { title: rowTitle })
        : __('messages.selections_item_unselected', { title: rowTitle });
}

function getRowTitle(row) {
    return row.title || row.name || row.label || row.id || __('item');
}

function handleRowClick(event, row, index) {
    if (! allowsSelections.value) return;

    // Check if the click target is an interactive element
    const target = event.target;
    const isInteractive = target.closest('button, a, input, select, textarea, [role="button"], [role="menuitem"], [role="option"], [data-interactive]');

    // If it's not an interactive element, fire the selection handler
    if (!isInteractive) {
        selectRow(row, index, event);
    }
}

function selectRow(row, index, event) {
    if (props.items === undefined) {
        selectionClicked(index, event);
        return;
    }

    const lastIndex = items.value.findIndex((item) => item.id === lastSubsetSelectionClicked);

    if (event?.shiftKey && lastIndex !== -1) {
        selectSubsetRange(Math.min(lastIndex, index), Math.max(lastIndex, index));
    } else {
        toggleSelection(row.id);
    }

    if (isSelected(row.id)) {
        lastSubsetSelectionClicked = row.id;
    }
}

function selectSubsetRange(from, to) {
    for (let i = from; i <= to; i++) {
        const id = items.value[i].id;

        if (!selections.value.includes(id) && !hasReachedSelectionLimit.value) {
            selections.value.push(id);
        }
    }
}
</script>

<template>
    <SortableList
        vertical
        :model-value="items"
        :mirror="false"
        item-class="sortable-row"
        handle-class="table-drag-handle"
        @update:model-value="reordered"
    >
        <tbody>
            <slot name="tbody-start" />
            <tr
                v-for="(row, index) in items"
                :key="row.id"
                class="sortable-row outline-hidden starting-style-transition"
                :data-row="isSelected(row.id) ? 'selected' : 'unselected'"
                @click="handleRowClick($event, row, index)"
            >
                <td class="table-drag-handle" v-if="reorderable"></td>
                <td class="checkbox-column" v-if="allowsSelections && !reorderable">
                    <Checkbox
                        :value="row.id"
                        :model-value="isSelected(row.id)"
                        :disabled="hasReachedSelectionLimit && allowsMultipleSelections && !isSelected(row.id)"
                        :label="getCheckboxLabel(row)"
                        :description="getCheckboxDescription(row)"
                        size="sm"
                        solo
                        @update:model-value="selectRow(row, index, $event)"
                    />
                </td>
                <td
                    v-for="column in visibleColumns"
                    :key="column.field"
                    :width="column.width"
                    :data-column="`${column.field}`"
                >
                    <slot
                        :name="`cell-${column.field}`"
                        :value="row[column.value || column.field]"
                        :row="row"
                        :is-column-visible="isColumnVisible"
                    >
                        <table-field
                            :handle="column.field"
                            :value="row[column.value || column.field]"
                            :values="row"
                            :fieldtype="column.fieldtype"
                            :key="column.field"
                        />
                    </slot>
                </td>
                <td class="actions-column" v-if="hasActions || $slots['prepended-row-actions']">
                    <RowActions :row="row">
                        <template v-if="$slots['prepended-row-actions']" #prepended-actions="{ row }">
                            <slot name="prepended-row-actions" :row="row" />
                        </template>
                    </RowActions>
                </td>
            </tr>
        </tbody>
    </SortableList>
</template>
