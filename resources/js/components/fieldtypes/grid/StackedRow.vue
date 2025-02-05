<template>
    <div
        class="replicator-set mb-4 rounded border shadow-sm dark:border-dark-900"
        :class="[sortableItemClass, { 'opacity-50': isExcessive }]"
    >
        <div class="replicator-set-header">
            <div class="item-move sortable-handle cursor-grab" :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div class="replicator-set-header-inner flex w-full items-end justify-end py-2 ltr:pl-2 rtl:pr-2">
                <button
                    v-if="canAddRows"
                    class="group flex items-center self-end ltr:mr-2 rtl:ml-2"
                    @click="$emit('duplicate', index)"
                    :aria-label="__('Duplicate Row')"
                >
                    <svg-icon name="light/duplicate" class="h-4 w-4 text-gray-600 group-hover:text-gray-900" />
                </button>
                <button
                    v-if="canDelete"
                    class="group flex items-center self-end"
                    @click="$emit('removed', index)"
                    :aria-label="__('Delete Row')"
                >
                    <svg-icon name="micro/trash" class="h-4 w-4 text-gray-600 group-hover:text-gray-900" />
                </button>
            </div>
        </div>

        <div class="replicator-set-body publish-fields @container">
            <set-field
                v-for="field in fields"
                v-show="showField(field, fieldPath(field.handle))"
                :key="field.handle"
                :field="field"
                :meta="meta[field.handle]"
                :value="values[field.handle]"
                :parent-name="name"
                :set-index="index"
                :errors="errors(field.handle)"
                :field-path="fieldPath(field.handle)"
                class="p-4"
                :read-only="grid.isReadOnly"
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </div>
    </div>
</template>

<style scoped>
.draggable-mirror {
    position: relative;
    z-index: 1000;
}
.draggable-source--is-dragging {
    opacity: 0.5;
}
</style>

<script>
import Row from './Row.vue';
import SetField from '../replicator/Field.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {
    mixins: [Row, ValidatesFieldConditions],

    components: { SetField },
};
</script>
