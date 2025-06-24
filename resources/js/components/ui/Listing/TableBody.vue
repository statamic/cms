<script setup>
import TableField from '@statamic/components/data-list/TableField.vue';
import RowActions from '@statamic/components/ui/Listing/RowActions.vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import Table from '@statamic/components/ui/Listing/Table.vue';

const { items, selections, reorderable, showBulkActions, visibleColumns, hasActions, maxSelections } =
    injectListingContext();

let lastItemClicked = null;
const reachedSelectionLimit = computed(() => selections.value.length === maxSelections);
const singleSelect = computed(() => maxSelections === 1);

function actualIndex(row) {
    return items.value.findIndex((item) => item.id === row.id);
}

function isSelected(id) {
    return selections.value.includes(id);
}

function checkboxClicked(row, index, event) {
    if (event.shiftKey && lastItemClicked !== null) {
        selectRange(Math.min(lastItemClicked, index), Math.max(lastItemClicked, index));
    } else {
        toggleSelection(row.id, index);
    }

    if (event.target.checked) {
        lastItemClicked = index;
    }
}

function toggleSelection(id) {
    const i = selections.value.indexOf(id);

    if (i > -1) {
        selections.value.splice(i, 1);
        return;
    }

    if (singleSelect.value) selections.value.pop();

    if (!reachedSelectionLimit.value) selections.value.push(id);
}

function selectRange(from, to) {
    for (let i = from; i <= to; i++) {
        let row = items.value[i].id;
        if (!selections.value.includes(row) && !reachedSelectionLimit.value) {
            selections.value.push(row);
        }
    }
}
</script>

<template>
    <tbody>
        <slot name="tbody-start" />
        <tr
            v-for="(row, index) in items"
            :key="row.id"
            class="sortable-row outline-hidden"
            :data-row="isSelected(row.id) ? 'selected' : 'unselected'"
        >
            <td class="table-drag-handle" v-if="reorderable"></td>
            <td class="checkbox-column" v-if="showBulkActions && !reorderable">
                <input
                    v-if="!reorderable"
                    type="checkbox"
                    :value="row.id"
                    :checked="isSelected(row.id)"
                    :disabled="reachedSelectionLimit && !singleSelect && !isSelected(row.id)"
                    :id="`checkbox-${row.id}`"
                    @click="checkboxClicked(row, index, $event)"
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
            <td class="actions-column" v-if="hasActions">
                <RowActions :row="row">
                    <template v-if="$slots['prepended-row-actions']" #prepended-actions="{ row }">
                        <slot name="prepended-row-actions" :row="row" />
                    </template>
                </RowActions>
            </td>
        </tr>
    </tbody>
</template>
