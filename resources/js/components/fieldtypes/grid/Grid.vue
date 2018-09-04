<template>

    <div>

        <grid-table
            :fields="fields"
            :rows="rows"
            :name="name"
            @updated="updated"
            @removed="removed"
        />

        <button @click.prevent="addRow" class="btn">Add Row</button>

    </div>

</template>

<script>
import GridTable from './Table.vue';

export default {

    mixins: [Fieldtype],

    components: { GridTable },

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
