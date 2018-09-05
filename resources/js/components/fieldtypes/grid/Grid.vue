<template>

    <div>

        <component
            :is="component"
            :fields="fields"
            :rows="rows"
            :name="name"
            @updated="updated"
            @removed="removed"
            @sorted="sorted"
        />

        <button @click.prevent="addRow" class="btn">Add Row</button>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import GridTable from './Table.vue';
import GridStacked from './Stacked.vue';

export default {

    mixins: [Fieldtype],

    components: {
        GridTable,
        GridStacked
    },

    data() {
        return {
            rows: null
        }
    },

    computed: {

        component() {
            return this.config.mode === 'stacked' ? 'GridStacked' : 'GridTable';
        },

        fields() {
            return this.config.fields;
        }

    },

    created() {
        // Rows should be cloned so we don't unintentionally modify the prop.
        let rows = _.clone(this.value || []);

        // Assign each row a unique id that Vue can use as a v-for key.
        this.rows = rows.map(row => Object.assign(row, { _id: uniqid() }));

        if (this.config.min_rows) {
            const rowsToAdd = this.config.min_rows - this.rows.length;
            for (var i = 1; i <= rowsToAdd; i++) this.addRow();
        }
    },

    methods: {

        addRow() {
            const row = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => null)
                .value();

            row._id = uniqid(); // Assign a unique id that Vue can use as a v-for key.

            this.rows.push(row);
        },

        updated(index, row) {
            this.rows.splice(index, 1, row);
        },

        removed(index) {
            if (confirm(translate('Are you sure?'))) {
                this.rows.splice(index, 1);
            }
        },

        sorted(rows) {
            this.rows = rows;
        }

    },

    watch: {

        rows(rows) {
            this.$emit('updated', rows);
        }

    }

}
</script>
