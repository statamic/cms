<template>

    <div
        class="replicator-set shadow-sm mb-4 rounded border dark:border-dark-900"
        :class="[sortableItemClass, { 'opacity-50': isExcessive }]"
    >

        <div class="replicator-set-header">
            <div class="item-move cursor-grab sortable-handle" :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div class="py-2 rtl:pr-2 ltr:pl-2 replicator-set-header-inner flex justify-end items-end w-full">
                <button v-if="canAddRows" class="flex self-end group items-center rtl:ml-2 ltr:mr-2" @click="$emit('duplicate', index)" :aria-label="__('Duplicate Row')">
                    <svg-icon name="light/duplicate" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
                </button>
                <button v-if="canDelete" class="flex self-end group items-center" @click="$emit('removed', index)" :aria-label="__('Delete Row')">
                    <svg-icon name="micro/trash" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
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

    mixins: [
        Row,
        ValidatesFieldConditions,
    ],

    components: { SetField },

}
</script>
