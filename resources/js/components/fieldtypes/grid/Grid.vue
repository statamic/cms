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
            :meta="meta.existing"
            :name="name"
            @updated="updated"
            @meta-updated="updateRowMeta"
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
import ManagesRowMeta from './ManagesRowMeta';

export default {

    mixins: [
        Fieldtype,
        ManagesRowMeta
    ],

    components: {
        GridTable,
        GridStacked
    },

    data() {
        return {
            rows: this.value,
            containerWidth: null,
            focused: false,
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

            this.updateRowMeta(id, this.meta.new);
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
            const old_id = row._id;
            row._id = uniqid();

            this.updateRowMeta(row._id, this.meta.existing[old_id]);
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
        },

    }

}
</script>
