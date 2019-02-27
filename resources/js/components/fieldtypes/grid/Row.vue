<template>

    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
        <grid-cell
            v-for="(field, i) in fields"
            v-show="showField(field)"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            :index="i"
            :row-index="index"
            :grid-name="name"
            @updated="updated"
            :class="sortableHandleClass"
        />

        <td class="row-controls">
            <dropdown-list>
                <ul class="dropdown-menu">
                    <li><a @click="$emit('duplicate', index)" v-text="__('Duplicate Row')"></a></li>
                    <li class="warning"><a @click="$emit('removed', index)" v-text="__('Delete Row')"></a></li>
                </ul>
            </dropdown-list>
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
import FieldConditions from '../../publish/FieldConditions.js';

export default {

    components: { GridCell },

    mixins: [FieldConditions],

    inject: ['storeName'],

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
