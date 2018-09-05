<template>

    <table class="data-table w-full mb-2 border">
        <thead>
            <tr>
                <grid-header-cell
                    v-for="field in fields"
                    :key="field.handle"
                    :field="field"
                />
                <th></th>
            </tr>
        </thead>
        <sortable-list
            v-model="sortableRows"
            :vertical="true"
            :handle-class="sortableHandleClass"
        >
            <tbody slot-scope="{ items: rows }">
                <sortable-item
                    v-for="(row, index) in rows"
                    :key="`row-${row._id}`">
                    <grid-row
                        :index="index"
                        :fields="fields"
                        :values="row"
                        :name="name"
                        @updated="(row, value) => $emit('updated', row, value)"
                        @removed="(row) => $emit('removed', row)"
                    />
                </sortable-item>
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
