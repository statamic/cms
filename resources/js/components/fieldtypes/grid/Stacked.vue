<template>

    <sortable-list
        v-model="sortableRows"
        :vertical="true"
        :item-class="sortableItemClass"
        :handle-class="sortableHandleClass"
    >
        <div class="grid-stacked" slot-scope="{}">
            <stacked-row
                v-for="(row, index) in rows"
                :key="`row-${row._id}`"
                :index="index"
                :fields="fields"
                :values="row"
                :name="name"
                @updated="(row, value) => $emit('updated', row, value)"
                @removed="(row) => $emit('removed', row)"
            />
        </div>
    </sortable-list>

</template>

<script>
import View from './View.vue';
import StackedRow from './StackedRow.vue';
import { SortableList } from '../../sortable/Sortable';

export default {

    mixins: [View],

    components: {
        StackedRow,
        SortableList
    }

}
</script>
