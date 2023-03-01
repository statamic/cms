<template>

    <element-container @resized="containerWidth = $event.width">
    <div class="grid-fieldtype-container">

        <small v-if="hasExcessRows" class="help-block text-red">
            {{ __('Max Rows') }}: {{ maxRows }}
        </small>
        <small v-else-if="hasNotEnoughRows" class="help-block text-red">
            {{ __('Min Rows') }}: {{ minRows }}
        </small>

        <component
            :is="component"
            :fields="fields"
            :rows="value"
            :meta="meta.existing"
            :name="name"
            :can-delete-rows="canDeleteRows"
            :can-add-rows="canAddRows"
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
            v-text="addRowButtonLabel"
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

        minRows() {
            return this.config.min_rows || 0;
        },

        maxRows() {
            return this.config.max_rows || Infinity;
        },

        canAddRows() {
            return ! this.isReadOnly && this.value.length < this.maxRows;
        },

        canDeleteRows() {
            return ! this.isReadOnly && this.value.length > this.minRows;
        },

        addRowButtonLabel() {
            return this.config.add_row || __('Add Row');
        },

        hasMaxRows() {
            return this.maxRows != null;
        },

        hasExcessRows() {
            return (this.value.length - this.maxRows) > 0;
        },

        hasNotEnoughRows() {
            return (this.value.length - this.minRows) < 0;
        },

        isReorderable() {
            return !this.isReadOnly && this.config.reorderable && this.maxRows > 1
        },

        replicatorPreview() {
            return `${this.config.display}: ${__n(':count row|:count rows', this.value.length)}`;
        }

    },

    reactiveProvide: {
        name: 'grid',
        include: ['config', 'isReorderable', 'isReadOnly', 'handle', 'fieldPathPrefix']
    },

    watch: {

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
            this.update([...this.value, row]);
        },

        updated(index, row) {
            this.update([
                ...this.value.slice(0, index),
                row,
                ...this.value.slice(index + 1)
            ]);
        },

        removed(index) {
            if (! confirm(__('Are you sure?'))) return;

            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1)
            ]);
        },

        duplicate(index) {
            const row = clone(this.value[index]);
            const old_id = row._id;
            row._id = uniqid();

            this.updateRowMeta(row._id, this.meta.existing[old_id]);

            this.update([...this.value, row]);
        },

        sorted(rows) {
            this.update(rows);
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
