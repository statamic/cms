<template>

    <tr>
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
            <span class="icon icon-cross delete" @click="$emit('removed', index)"></span>
        </td>
    </tr>

</template>

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

    methods: {

        updated(handle, value) {
            let row = JSON.parse(JSON.stringify(this.values));
            row[handle] = value;
            this.$emit('updated', this.index, row);
        }

    }

}
</script>
