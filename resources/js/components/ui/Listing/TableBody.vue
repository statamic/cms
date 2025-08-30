<script setup>
import TableField from '@/components/data-list/TableField.vue';
import RowActions from '@/components/ui/Listing/RowActions.vue';
import SortableList from '@/components/sortable/SortableList.vue';
import { injectListingContext } from '@/components/ui/Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import Table from '@/components/ui/Listing/Table.vue';
import { Checkbox } from '@/components/ui';

const {
    items,
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

function isSelected(id) {
    return selections.value.includes(id);
}

function getCheckboxLabel(row) {
    const rowTitle = getRowTitle(row);
    return isSelected(row.id)
        ? __('deselect_title', { title: rowTitle })
        : __('select_title', { title: rowTitle });
}

function getCheckboxAriaLabel(row) {
    const rowTitle = getRowTitle(row);
    return isSelected(row.id)
        ? __('deselect_title', { title: rowTitle })
        : __('select_title', { title: rowTitle });
}

function getCheckboxDescription(row) {
    const rowTitle = getRowTitle(row);
    const isDisabled = hasReachedSelectionLimit.value && allowsMultipleSelections.value && !isSelected(row.id);

    if (isDisabled) {
        return __('selection_limit_reached', { title: rowTitle });
    }

    return isSelected(row.id)
        ? __('item_selected_description', { title: rowTitle })
        : __('item_not_selected_description', { title: rowTitle });
}

function getRowTitle(row) {
    // Try to get a meaningful title from common fields
    return row.title || row.name || row.label || row.id || __('item');
}

function handleRowClick(event, index) {
    if (! allowsSelections.value) return;

    // Check if the click target is an interactive element
    const target = event.target;
    const isInteractive = target.closest('button, a, input, select, textarea, [role="button"], [role="menuitem"], [role="option"], [data-interactive]');

    // If it's not an interactive element, fire the selection handler
    if (!isInteractive) {
        selectionClicked(index, event);
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
                class="sortable-row outline-hidden"
                :data-row="isSelected(row.id) ? 'selected' : 'unselected'"
                @click="handleRowClick($event, index)"
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
                        @update:model-value="selectionClicked(index, $event)"
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
            <!-- <td class="type-column" v-if="type">
                <Badge
                    size="sm"
                    v-if="type === 'entries' || type === 'terms'"
                    :label="type === 'entries' ? __(row.collection.title) : __(row.taxonomy.title)"
                />
            </td> -->
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
