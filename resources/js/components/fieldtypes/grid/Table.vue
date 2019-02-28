<template>

    <table class="grid-table" v-if="rows.length > 0">
        <thead>
            <tr>
                <th class="grid-drag-handle-header" v-if="reorderable"></th>
                <grid-header-cell
                    v-for="field in fields"
                    :key="field.handle"
                    :field="field"
                />
                <th class="row-controls"></th>
            </tr>
        </thead>
        <sortable-list
            v-model="sortableRows"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
        >
            <tbody slot-scope="{}">
                <grid-row
                    v-for="(row, index) in rows"
                    :key="`row-${row._id}`"
                    :index="index"
                    :fields="fields"
                    :values="row"
                    :name="name"
                    @updated="(row, value) => $emit('updated', row, value)"
                    @duplicate="(row) => $emit('duplicate', row)"
                    @removed="(row) => $emit('removed', row)"
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

    inject: ['reorderable'],

    components: {
        GridRow,
        GridHeaderCell,
        SortableList,
        SortableItem
    }

}
</script>
