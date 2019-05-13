<template>

    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
        <td :class="sortableHandleClass" v-if="grid.isReorderable"></td>
        <grid-cell
            v-for="(field, i) in fields"
            :show-inner="showField(field)"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :index="i"
            :row-index="index"
            :grid-name="name"
            @updated="updated(field.handle, $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <td class="row-controls" v-if="!grid.isReadOnly">
            <dropdown-list ref="dropdown">
                <ul class="dropdown-menu">
                    <li><a @click="duplicate(index)" v-text="__('Duplicate Row')"></a></li>
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
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    components: { GridCell },

    mixins: [ValidatesFieldConditions],

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
        meta: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        }
    },

    inject: [
        'grid',
        'sortableItemClass',
        'sortableHandleClass',
    ],

    computed: {

        isExcessive() {
            const max = this.grid.config.max_rows;
            if (! max) return false;
            return this.index >= max;
        }

    },

    methods: {
        duplicate(index) {
            this.$emit('duplicate', index);
            this.$refs.dropdown.close();
        },

        updated(handle, value) {
            let row = JSON.parse(JSON.stringify(this.values));
            row[handle] = value;
            this.$emit('updated', this.index, row);
        }

    }

}
</script>
