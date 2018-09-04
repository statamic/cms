<template>

    <div>

        <table class="data-table w-full mb-2 border">
            <thead>
                <tr>
                    <grid-header-cell
                        v-for="field in config.fields"
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
                    @updated="updated"
                    @removed="removed"
                />
            </tbody>
        </table>

        <button @click.prevent="addRow" class="btn">Add Row</button>

    </div>

</template>

<script>
import GridRow from './Row.vue';
import GridHeaderCell from './HeaderCell.vue';

export default {

    mixins: [Fieldtype],

    components: {
        GridRow,
        GridHeaderCell,
    },

    data() {
        return {
            rows: _.clone(this.value || [])
        }
    },

    computed: {

        fields() {
            return this.config.fields;
        }

    },

    methods: {

        addRow() {
            const row = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => null)
                .value();

            this.rows.push(row);
        },

        updated(index, row) {
            this.rows.splice(index, 1, row);
        },

        removed(index) {
            if (confirm(translate('Are you sure?'))) {
                this.rows.splice(index, 1);
            }
        }

    },

    watch: {

        rows(rows) {
            this.$emit('updated', rows);
        }

    }

}
</script>
