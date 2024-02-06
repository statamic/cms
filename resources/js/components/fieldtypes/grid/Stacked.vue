<template>
<div>
    <div class="flex justify-end absolute top-3 right-3 @md:right-6" v-if="! grid.fullScreenMode">
        <button v-if="allowFullscreen" @click="grid.toggleFullScreen" class="btn btn-icon flex items-center" v-tooltip="__('Toggle Fullscreen Mode')">
            <svg-icon name="expand-bold" class="h-3.5 px-0.5 text-gray-750" v-show="! grid.fullScreenMode" />
            <svg-icon name="shrink-all" class="h-3.5 px-0.5 text-gray-750" v-show="grid.fullScreenMode" />
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
        <div
            class="grid-stacked"
            :class="{
                'mt-12': !grid.fullScreenMode && hideDisplay,
                'mt-6': !grid.fullScreenMode && !hideDisplay,
            }"
            slot-scope="{}"
        >
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
    },

}
</script>
