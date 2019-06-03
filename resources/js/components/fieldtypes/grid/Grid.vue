<template>

    <element-container @resized="containerWidth = $event.width">
    <div class="grid-fieldtype-container">

        <small v-if="hasExcessRows" class="help-block text-red">
            {{ __('Max Rows') }}: {{ maxRows }}
        </small>

        <component
            :is="component"
            :fields="fields"
            :rows="rows"
            :meta="metas"
            :name="name"
            @updated="updated"
            @removed="removed"
            @duplicate="duplicate"
            @sorted="sorted"
            @focus="focused = true"
            @blur="blurred"
        />

        <button
            class="btn"
            v-if="canAddRows"
            v-text="__('Add Row')"
            @click.prevent="addRow" />

    </div>
    </element-container>

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
            rows: null,
            containerWidth: null,
            focused: false,
            metas: {}
        }
    },

    computed: {

        component() {
            const isNarrow = this.fields.length > 1 && this.containerWidth < 600;

            return this.config.mode === 'stacked' || isNarrow
                ? 'GridStacked'
                : 'GridTable';
        },

        fields() {
            return this.config.fields;
        },

        maxRows() {
            return this.config.max_rows || Infinity;
        },

        canAddRows() {
            return !this.isReadOnly && this.rows.length < this.maxRows;
        },

        hasMaxRows() {
            return this.maxRows != null;
        },

        hasExcessRows() {
            if (! this.hasMaxRows) return false;
            return (this.rows.length - this.maxRows) > 0;
        },

        isReorderable() {
            return !this.isReadOnly && this.config.reorderable && this.maxRows > 1
        },

    },

    reactiveProvide: {
        name: 'grid',
        include: ['config', 'isReorderable', 'isReadOnly']
    },

    watch: {

        value: {
            immediate: true,
            handler(value) {
                if (value.length === 0) {
                    this.rows = value;
                    return;
                }

                if (value[0].hasOwnProperty('_id')) {
                    this.rows = value;
                    return;
                }

                let rows = value || [];
                let metas = {};

                rows = rows.map((row, index) => {
                    let id = uniqid();
                    metas[id] = this.meta.existing[index];
                    return Object.assign(row, { _id: id });
                });

                this.metas = metas;
                this.rows = rows;

                if (this.config.min_rows) {
                    const rowsToAdd = this.config.min_rows - this.rows.length;
                    for (var i = 1; i <= rowsToAdd; i++) this.addRow();
                }
            }
        },

        rows(rows) {
            this.update(rows);
        },

        isReorderable: {
            immediate: true,
            handler(reorderable) {
                this.reorderable = reorderable;
            }
        },

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        }

    },

    methods: {

        addRow() {
            const id = uniqid();

            const row = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => this.meta.defaults[field.handle])
                .value();

            row._id = id;

            this.metas[id] = this.meta.new;
            this.rows.push(row);
        },

        updated(index, row) {
            this.rows.splice(index, 1, row);
        },

        removed(index) {
            if (confirm(__('Are you sure?'))) {
                this.rows.splice(index, 1);
            }
        },

        duplicate(index) {
            const row = _.clone(this.rows[index]);

            row._id = uniqid();

            this.rows.push(row);
        },

        sorted(rows) {
            this.rows = rows;
        },

        getReplicatorPreviewText() {
            // TODO
        },

        focus() {
            // TODO
        },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        }

    }

}
</script>
