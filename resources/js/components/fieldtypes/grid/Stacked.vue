<template>

    <sortable-list
        :value="rows"
        :vertical="true"
        :item-class="sortableItemClass"
        :handle-class="sortableHandleClass"
        @dragstart="$emit('focus')"
        @dragend="$emit('blur')"
        @input="(rows) => $emit('sorted', rows)"
    >
        <div class="grid-stacked" slot-scope="{}">
            <stacked-row
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
                @removed="(row) => $emit('removed', row)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
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
