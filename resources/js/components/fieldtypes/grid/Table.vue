<template>

    <table class="data-table w-full mb-2 border">
        <thead>
            <tr>
                <grid-header-cell
                    v-for="field in fields"
                    :key="field.handle"
                    :field="field"
                />
                <th></th>
            </tr>
        </thead>
        <tbody>
            <grid-row
                v-for="(row, index) in rows"
                :key="`row-${index}`"
                :index="index"
                :fields="fields"
                :values="row"
                :name="name"
                @updated="(row, value) => $emit('updated', row, value)"
                @removed="(row) => $emit('removed', row)"
            />
        </tbody>
    </table>

</template>

<script>
import GridRow from './Row.vue';
import GridHeaderCell from './HeaderCell.vue';

export default {

    components: {
        GridRow,
        GridHeaderCell,
    },

    props: ['fields', 'rows', 'name'],

    methods: {
        updated(v, a) {
            console.log(v, a);
        }
    }

}
</script>
