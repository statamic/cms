<template>

    <div
        class="bg-grey-10 shadow-sm mb-2 rounded border"
        :class="[sortableItemClass, { 'opacity-50': isExcessive }]"
    >
        <div
            class="grid-item-header"
            :class="{ [sortableHandleClass]: grid.isReorderable }"
        >
            <div />
            <button v-if="canDelete" class="icon icon-cross cursor-pointer" @click="$emit('removed', index)" :aria-label="__('Delete Row')" />
        </div>
        <publish-fields-container>
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
                class="p-2"
                :read-only="grid.isReadOnly"
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </publish-fields-container>
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
