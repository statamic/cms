<template>

    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
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

export default {

    components: { GridCell },

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

    inject: ['gridConfig', 'sortableItemClass', 'sortableHandleClass'],

    computed: {

        isExcessive() {
            const max = this.gridConfig.max_rows;
            if (! max) return false;
            return this.index >= max;
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
