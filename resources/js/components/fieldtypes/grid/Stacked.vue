<template>
    <div>
        <sortable-list
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            append-to="body"
            constrain-dimensions
            :model-value="rows"
            @update:model-value="(rows) => $emit('sorted', rows)"
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
        >
            <div
                class="grid-stacked"
                :class="{
                    'mt-0': !allowFullscreen && hideDisplay,
                    'mt-4': !hideDisplay,
                    'mt-10': allowFullscreen,
                }"
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
                    @duplicate="(row) => $emit('duplicate', row)"
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
};
</script>
