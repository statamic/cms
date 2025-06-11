<script setup>
import { Button, Panel, PanelFooter } from '@statamic/ui';
import { ref, computed, useTemplateRef } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import TableField from '@statamic/components/data-list/TableField.vue';
import ToggleAll from './ToggleAll.vue';
import Pagination from './Pagination.vue';
import HeaderCell from './HeaderCell.vue';

const props = defineProps({
    unstyled: {
        type: Boolean,
        default: false,
    },
    contained: {
        type: Boolean,
        default: false,
    },
    reorderable: {
        type: Boolean,
        default: false,
    },
});

const { visibleColumns, selections, items, allowBulkActions, maxSelections, sortColumn, setSortColumn, loading } =
    injectListingContext();
const tableRef = useTemplateRef('table');
const shifting = ref(false);
let lastItemClicked = null;
const hasSelections = computed(() => selections.value.length > 0);
const reachedSelectionLimit = computed(() => selections.value.length === maxSelections);
const singleSelect = computed(() => maxSelections === 1);

const relativeColumnsSize = computed(() => {
    if (visibleColumns.value.length <= 4) return 'sm';
    if (visibleColumns.value.length <= 8) return 'md';
    if (visibleColumns.value.length >= 12) return 'lx';
    return 'xl';
});

function actualIndex(row) {
    return items.value.findIndex((item) => item.id === row.id);
}

function isSelected(id) {
    return selections.value.includes(id);
}

function checkboxClicked(row, index, event) {
    if (event.shiftKey && lastItemClicked !== null) {
        tableRef.value.focus();
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
    <Panel class="relative overflow-x-auto overscroll-x-contain">
        <table
            :data-size="relativeColumnsSize"
            :class="{
                'select-none': shifting,
                'data-table': !unstyled,
                contained: contained,
                'opacity-50': loading,
            }"
            data-table
            ref="table"
            tabindex="0"
            :data-has-selections="hasSelections ? true : null"
            @keydown.shift="shifting = true"
            @keyup="shifting = false"
        >
            <thead v-if="allowBulkActions || visibleColumns.length > 1">
                <tr>
                    <th
                        v-if="allowBulkActions || reorderable"
                        :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
                    >
                        <ToggleAll v-if="allowBulkActions && !singleSelect" />
                    </th>
                    <HeaderCell v-for="column in visibleColumns" :key="column.field" :column />
                    <!--                    <th class="type-column" v-if="type">-->
                    <!--                        <template v-if="type === 'entries'">{{ __('Collection') }}</template>-->
                    <!--                        <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>-->
                    <!--                    </th>-->
                    <th class="actions-column" />
                </tr>
            </thead>
            <tbody>
                <slot name="tbody-start" />
                <tr
                    v-for="(row, index) in items"
                    :key="row.id"
                    class="sortable-row outline-hidden"
                    :data-row="isSelected(row.id) ? 'selected' : 'unselected'"
                >
                    <td class="table-drag-handle" v-if="reorderable"></td>
                    <td class="checkbox-column" v-if="allowBulkActions && !reorderable">
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
                    <td class="actions-column">
                        <slot name="actions" :row="row" :index="actualIndex(row)" :display-index="index"></slot>
                    </td>
                </tr>
            </tbody>
        </table>
        <PanelFooter>
            <Pagination />
        </PanelFooter>
    </Panel>
</template>
