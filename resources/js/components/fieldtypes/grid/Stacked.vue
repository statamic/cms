<template>

    <sortable-list
        v-model="sortableRows"
        :vertical="true"
        :handle-class="sortableHandleClass"
    >
        <div class="grid-stacked" slot-scope="{ items: rows }">
            <sortable-item
                v-for="(row, index) in rows"
                :key="`row-${row._id}`">
                <stacked-row
                    :index="index"
                    :fields="fields"
                    :values="row"
                    :name="name"
                    @updated="(row, value) => $emit('updated', row, value)"
                    @removed="(row) => $emit('removed', row)"
                />
            </sortable-item>
        </div>
    </sortable-list>

</template>

<script>
import View from './View.vue';
import StackedRow from './StackedRow.vue';
import { SortableList, SortableItem } from '../../sortable/Sortable';

export default {

    mixins: [View],

    components: {
        StackedRow,
        SortableList,
        SortableItem
    }

}
</script>
