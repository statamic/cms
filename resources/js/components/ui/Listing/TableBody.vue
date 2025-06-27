<script setup>
import TableField from '@statamic/components/data-list/TableField.vue';
import RowActions from '@statamic/components/ui/Listing/RowActions.vue';
import SortableList from '@statamic/components/sortable/SortableList.vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import Table from '@statamic/components/ui/Listing/Table.vue';

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

function actualIndex(row) {
    return items.value.findIndex((item) => item.id === row.id);
}

function isSelected(id) {
    return selections.value.includes(id);
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
            >
                <td class="table-drag-handle" v-if="reorderable"></td>
                <td class="checkbox-column" v-if="allowsSelections && !reorderable">
                    <input
                        v-if="!reorderable"
                        type="checkbox"
                        :value="row.id"
                        :checked="isSelected(row.id)"
                        :disabled="hasReachedSelectionLimit && allowsMultipleSelections && !isSelected(row.id)"
                        :id="`checkbox-${row.id}`"
                        @click="selectionClicked(index, $event)"
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
                        :values="row"
                        :row="row"
                        :index="actualIndex(row)"
                        :display-index="index"
                        :checkbox-id="`checkbox-${row.id}`"
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
                <!--                    <td class="type-column" v-if="type">-->
                <!--                        <Badge-->
                <!--                            size="sm"-->
                <!--                            v-if="type === 'entries' || type === 'terms'"-->
                <!--                            :label="type === 'entries' ? __(row.collection.title) : __(row.taxonomy.title)"-->
                <!--                        />-->
                <!--                    </td>-->
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
