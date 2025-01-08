<template>
    <table v-if="rows.length > 0" class="grid-table">
        <thead>
            <tr>
                <th v-if="grid.isReorderable" class="grid-drag-handle-header"></th>
                <grid-header-cell
                    v-for="field in fields"
                    :key="field.handle"
                    :field="field"
                />
                <th class="grid-row-controls row-controls"></th>
            </tr>
        </thead>
        <sortable-list
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            append-to="body"
            :model-value="rows"
            @update:model-value="(rows) => $emit('sorted', rows)"
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
        >
            <tbody>
                <grid-row
                    v-for="(row, index) in rows"
                    :key="`row-${row._id}`"
                    :index="index"
                    :fields="fields"
                    :values="row"
                    :meta="meta[row._id]"
                    :name="name"
                    :field-path-prefix="fieldPathPrefix"
                    :can-delete="canDeleteRows"
                    :can-add-rows="canAddRows"
                    @updated="(row, value) => $emit('updated', row, value)"
                    @meta-updated="$emit('meta-updated', row._id, $event)"
                    @duplicate="(row) => $emit('duplicate', row)"
                    @removed="(row) => $emit('removed', row)"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                />
            </tbody>
        </sortable-list>
    </table>

</template>

<script>
import View from './View.vue';
import GridRow from './Row.vue';
import GridHeaderCell from './HeaderCell.vue';
import { SortableList, SortableItem } from '../../sortable/Sortable';

export default {
    mixins: [View],
    components: {
        GridRow,
        GridHeaderCell,
        SortableList,
        SortableItem
    }
}
</script>
