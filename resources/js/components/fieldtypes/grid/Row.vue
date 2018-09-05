<template>

    <tr :class="{ 'opacity-50': isExcessive }">
        <grid-cell
            v-for="(field, i) in fields"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            :index="i"
            :row-index="index"
            :grid-name="name"
            @updated="updated"
        />

        <td class="row-controls">
            <span class="icon icon-menu move cursor-move" :class="sortableHandleClass"></span>
            <span class="icon icon-cross delete" @click="$emit('removed', index)"></span>
        </td>
    </tr>

</template>

<style scoped>
    .draggable-mirror {
        display: none;
    }
</style>

<script>
import GridCell from './Cell.vue';
import { SortableHandle } from '../../sortable/Sortable';

export default {

    components: {
        GridCell,
        SortableHandle
    },

    props: {
        index: {
            type: Number,
            required: true
        },
        fields: {
            type: Array,
            required: true
        },
        values: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        }
    },

    inject: ['gridConfig'],

    computed: {

        isExcessive() {
            const max = this.gridConfig.max_rows;
            if (! max) return false;
            return this.index >= max;
        },

        sortableHandleClass() {
            return `${this.name}-drag-handle`;
        }

    },

    methods: {

        updated(handle, value) {
            let row = JSON.parse(JSON.stringify(this.values));
            row[handle] = value;
            this.$emit('updated', this.index, row);
        }

    }

}
</script>
