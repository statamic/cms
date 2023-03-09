<template>
<div>

    <div class="mb-5">
        <button @click="grid.toggleFullScreen" class="flex items-center w-full h-full justify-center text-gray-500 hover:text-gray-700">
            <svg-icon name="expand-2" class="h-3.5 w-3.5" v-show="! grid.fullScreenMode" />
            <svg-icon name="shrink-all" class="h-3.5 w-3.5" v-show="grid.fullScreenMode" />
        </button>
    </div>

    <sortable-list
        :value="rows"
        :vertical="true"
        :item-class="sortableItemClass"
        :handle-class="sortableHandleClass"
        append-to="body"
        constrain-dimensions
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

</div>
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
