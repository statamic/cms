<template>
    <table
        class="grid-table"
        :class="{
            'mb-4': (rows.length > 0 || usesSectionRowSortable) && !grid.usesExternalAddRow,
            'grid-table--sectioned': showsHeadersInSection && !grid.fullScreenMode,
        }"
        v-if="rows.length > 0 || usesSectionRowSortable"
    >
        <thead>
            <tr>
                <th class="w-3" v-if="grid.isReorderable"></th>
                <grid-header-cell v-for="field in fields" :key="field.handle" :field="field" />
                <th class="grid-row-controls row-controls"></th>
            </tr>
        </thead>
        <sortable-list
            :model-value="rows"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            :disabled="usesSectionRowSortable"
            append-to="body"
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
            @update:model-value="(rows) => $emit('sorted', rows)"
            v-slot="{}"
        >
            <tbody ref="zone" :class="{ 'publish-section-row-zone': usesSectionRowSortable }">
                <grid-row
                    v-for="(row, index) in rows"
                    :key="`row-${row._id}`"
                    :index="index"
                    :fields="fields"
                    :values="row"
                    :meta="meta[row._id]"
                    :name="name"
                    :field-path-prefix="fieldPathPrefix"
                    :meta-path-prefix="metaPathPrefix"
                    :can-delete="canDeleteRows"
                    :can-add-rows="canAddRows"
                    :has-error="rowHasError(row._id)"
                    :read-only
                    @updated="(row, value) => $emit('updated', row, value)"
                    @meta-updated="$emit('meta-updated', row._id, $event)"
                    @duplicate="(row) => $emit('duplicate', row)"
                    @removed="(row) => $emit('removed', row)"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                />
                <tr v-if="usesSectionRowSortable && !rows.length">
                    <td :colspan="emptyColspan" class="h-16"></td>
                </tr>
            </tbody>
        </sortable-list>
    </table>
</template>

<script>
import View from './View.vue';
import GridRow from './Row.vue';
import GridHeaderCell from './HeaderCell.vue';
import { SortableList } from '../../sortable/Sortable';

export default {
    mixins: [View],

    components: {
        GridRow,
        GridHeaderCell,
        SortableList,
    },

    computed: {
        emptyColspan() {
            return this.fields.length + (this.grid.isReorderable ? 1 : 0) + 1;
        },
    },
};
</script>
