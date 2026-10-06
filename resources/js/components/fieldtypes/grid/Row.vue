<template>
    <tr
        tabindex="-1"
        data-reorder-focus
        :class="[
            sortableItemClass,
            {
                'opacity-50': isExcessive,
                'inset-ring-1 inset-ring-red': hasError,
            },
        ]"
        :data-moving="moving || undefined"
        :aria-grabbed="moving ? 'true' : undefined"
    >
        <td v-if="grid.isReorderable" class="drag-handle relative">
            <DragHandle :class="sortableHandleClass" @keyboard-reorder="startMovingFromHandle" />
            <KeyboardReorderIndicator :moving="moving && moveOrigin === 'start'" side="start" />
        </td>

        <FieldsProvider
            :fields="fields"
            :as-config="false"
            :read-only
            :field-path-prefix="`${fieldPathPrefix}.${index}`"
            :meta-path-prefix="`${metaPathPrefix}.existing.${values._id}`"
        >
            <grid-cell v-for="(field, i) in fields" :key="field.handle" :field="field" />
        </FieldsProvider>

        <td class="grid-row-controls row-controls relative" v-if="showRowControls">
            <button
                ref="rootEl"
                type="button"
                class="sr-only"
                :tabindex="moving ? 0 : -1"
                :aria-label="__('Use up and down arrows to reorder. Press Enter or Escape when finished.')"
            />
            <KeyboardReorderIndicator :moving="moving && moveOrigin === 'end'" side="end" />
            <Dropdown placement="left-start">
                <DropdownMenu>
                    <DropdownItem
                        v-if="grid.isReorderable"
                        :text="__('Move')"
                        @click="startMovingFromMenu"
                    />
                    <DropdownItem v-if="canAddRows" :text="__('Duplicate Row')" icon="duplicate" @click="$emit('duplicate', index)" />
                    <DropdownItem v-if="canDelete" :text="__('Delete Row')" icon="trash" variant="destructive" @click="$emit('removed', index, fields)" />
                </DropdownMenu>
            </Dropdown>
            <div class="sr-only" aria-live="assertive">{{ status }}</div>
        </td>
    </tr>
</template>

<style scoped>
.draggable-mirror {
    display: none;
}
</style>

<script>
import GridCell from './Cell.vue';
import { DragHandle, Dropdown, DropdownMenu, DropdownItem, PublishFieldsProvider as FieldsProvider } from '@ui';
import { useKeyboardItemReorder } from '@/composables/keyboard-item-reorder.js';
import KeyboardReorderIndicator from '@/components/sortable/KeyboardReorderIndicator.vue';

export default {
    components: { DragHandle, Dropdown, DropdownMenu, DropdownItem, FieldsProvider, GridCell, KeyboardReorderIndicator },

    props: {
        index: {
            type: Number,
            required: true,
        },
        fields: {
            type: Array,
            required: true,
        },
        values: {
            type: Object,
            required: true,
        },
        meta: {
            type: Object,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        fieldPathPrefix: {
            type: String,
        },
        metaPathPrefix: {
            type: String,
        },
        canDelete: {
            type: Boolean,
            default: true,
        },
        canAddRows: {
            type: Boolean,
            default: true,
        },
        totalRows: {
            type: Number,
            required: true,
        },
        hasError: {
            type: Boolean,
            default: false,
        },
        readOnly: {
            type: Boolean,
        },
    },

    inject: ['grid', 'sortableItemClass', 'sortableHandleClass'],

    setup(props, { emit }) {
        return useKeyboardItemReorder({
            index: () => props.index,
            total: () => props.totalRows,
            onMove: (from, to) => emit('moved', from, to),
        });
    },

    data() {
        return {
            extraValues: {},
        };
    },

    computed: {
        isExcessive() {
            const max = this.grid.config.max_rows;
            if (!max) return false;
            return this.index >= max;
        },

        showRowControls() {
            return !this.grid.isReadOnly && (this.canAddRows || this.canDelete || this.grid.isReorderable);
        },
    },

    methods: {
        startMovingFromHandle() {
            this.startMoving('start');
        },

        startMovingFromMenu() {
            this.startMoving('end');
        },

        updated(handle, value) {
            this.$emit('updated', this.index, { ...this.values, [handle]: value });
        },

        metaUpdated(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

        fieldPath(handle) {
            return `${this.fieldPathPrefix}.${this.index}.${handle}`;
        },
    },
};
</script>
