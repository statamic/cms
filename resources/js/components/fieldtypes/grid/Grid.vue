<template>

    <div>

        <small v-if="hasExcessRows" class="help-block text-red">
            Only {{ maxRows }} rows are allowed.
        </small>

        <component
            :is="component"
            :fields="fields"
            :rows="rows"
            :name="name"
            @updated="updated"
            @removed="removed"
            @sorted="sorted"
        />

        <button
            class="btn"
            v-if="canAddRows"
            v-text="translate('Add Row')"
            @click.prevent="addRow" />

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
            // TODO: Should become stacked at <600px
            return this.config.mode === 'stacked' ? 'GridStacked' : 'GridTable';
        },

        fields() {
            return this.config.fields;
        },

        maxRows() {
            return this.config.max_rows || Infinity;
        },

        canAddRows() {
            return this.rows.length < this.maxRows;
        },

        hasMaxRows() {
            return this.maxRows != null;
        },

        hasExcessRows() {
            if (! this.hasMaxRows) return false;
            return (this.rows.length - this.maxRows) > 0;
        }

    },

    provide() {
        return {
            gridConfig: this.config
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
        },

        getReplicatorPreviewText() {
            // TODO
        },

        focus() {
            // TODO
        }

    },

    watch: {

        rows(rows) {
            this.$emit('updated', rows);
        }

    }

}
</script>
