<template>
    <portal name="grid-fullscreen" :disabled="!fullScreenMode" :provide="provide">
        <element-container @resized="containerWidth = $event.width">
            <div :class="{ '@apply fixed inset-0 min-h-screen overflow-scroll rounded-none bg-gray-100 dark:bg-gray-900 z-998': fullScreenMode }">
                <publish-field-fullscreen-header
                    v-if="fullScreenMode"
                    :title="config.display"
                    :field-actions="fieldActions"
                    @close="fullScreenMode = false"
                >
                </publish-field-fullscreen-header>

                <section :class="{ 'mt-14 p-4': fullScreenMode }">
                    <small v-if="hasExcessRows" class="help-block text-red-500">
                        {{ __('Max Rows') }}: {{ maxRows }}
                    </small>
                    <small v-else-if="hasNotEnoughRows" class="help-block text-red-500">
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
                        :allow-fullscreen="config.fullscreen"
                        :hide-display="config.hide_display"
                        @updated="updated"
                        @meta-updated="updateRowMeta"
                        @removed="removed"
                        @duplicate="duplicate"
                        @sorted="sorted"
                        @focus="focused = true"
                        @blur="blurred"
                    />

                    <ui-button size="sm" v-if="canAddRows" v-text="__(addRowButtonLabel)" @click.prevent="addRow" />
                </section>
            </div>
        </element-container>

        <confirmation-modal
            v-if="deletingRow"
            :title="__('Delete Row')"
            :body-text="__('Are you sure?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="confirmDelete"
            @cancel="deletingRow = null"
        />
    </portal>
</template>

<script>
import Fieldtype from '../Fieldtype.vue';
import uniqid from 'uniqid';
import GridTable from './Table.vue';
import GridStacked from './Stacked.vue';
import ManagesRowMeta from './ManagesRowMeta';

export default {
    mixins: [Fieldtype, ManagesRowMeta],

    components: {
        GridTable,
        GridStacked,
    },

    data() {
        return {
            containerWidth: null,
            focused: false,
            fullScreenMode: false,
            deletingRow: null,
            provide: {
                grid: this.makeGridProvide(),
            },
        };
    },

    provide: {
        isInGridField: true,
    },

    computed: {
        component() {
            const isNarrow = this.fields.length > 1 && this.containerWidth < 600;

            return this.config.mode === 'stacked' || isNarrow ? 'GridStacked' : 'GridTable';
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
            return !this.isReadOnly && this.value.length < this.maxRows;
        },

        canDeleteRows() {
            return !this.isReadOnly && this.value.length > this.minRows;
        },

        addRowButtonLabel() {
            return __(this.config.add_row) || __('Add Row');
        },

        hasMaxRows() {
            return this.maxRows != null;
        },

        hasExcessRows() {
            return this.value.length - this.maxRows > 0;
        },

        hasNotEnoughRows() {
            return this.value.length - this.minRows < 0;
        },

        isReorderable() {
            return !this.isReadOnly && this.config.reorderable && this.maxRows > 1;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return `${__(this.config.display)}: ${__n(':count row|:count rows', this.value.length)}`;
        },

        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.fullScreenMode ? 'shrink-all' : 'expand-bold'),
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullScreen,
                },
            ];
        },
    },

    watch: {
        isReorderable: {
            immediate: true,
            handler(reorderable) {
                this.reorderable = reorderable;
            },
        },

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        },
    },

    methods: {
        addRow() {
            const id = uniqid();

            const row = Object.fromEntries(
                this.fields.map((field) => [field.handle, this.meta.defaults[field.handle]]),
            );

            row._id = id;

            this.updateRowMeta(id, this.meta.new);
            this.update([...this.value, row]);
        },

        updated(index, row) {
            this.update([...this.value.slice(0, index), row, ...this.value.slice(index + 1)]);
        },

        removed(index) {
            // if the row is empty, don't show the confirmation. this.value[index] is an object with the row data
            const row = this.value[index];
            const emptyRow = Object.fromEntries(
                this.fields.map((field) => [field.handle, this.meta.defaults[field.handle]]),
            );

            // Check if the row has been modified from its default state
            const hasChanges = this.fields.some(field => row[field.handle] !== emptyRow[field.handle]);

            if (hasChanges) {
                this.deletingRow = index;
                return;
            }

            this.update([...this.value.slice(0, index), ...this.value.slice(index + 1)]);
        },

        confirmDelete() {
            this.update([...this.value.slice(0, this.deletingRow), ...this.value.slice(this.deletingRow + 1)]);
            this.deletingRow = null;
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

        toggleFullScreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },

        makeGridProvide() {
            const grid = {};
            Object.defineProperties(grid, {
                config: { get: () => this.config },
                isReorderable: { get: () => this.isReorderable },
                isReadOnly: { get: () => this.isReadOnly },
                handle: { get: () => this.handle },
                fieldPathPrefix: { get: () => this.fieldPathPrefix },
                metaPathPrefix: { get: () => this.metaPathPrefix },
                fullScreenMode: { get: () => this.fullScreenMode },
                toggleFullScreen: { get: () => this.toggleFullScreen },
            });
            return grid;
        },
    },
};
</script>
