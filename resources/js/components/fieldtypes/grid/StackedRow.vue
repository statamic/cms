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
            <publish-field
                v-for="field in fields"
                v-show="showField(field)"
                :key="field.handle"
                :config="{...field, localizable: grid.config.localizable}"
                :value="values[field.handle]"
                :meta="meta[field.handle]"
                :read-only="grid.isReadOnly"
                :name-prefix="namePrefix"
                :errors="errors(field.handle)"
                class="p-2"
                @input="updated(field.handle, $event)"
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
import PublishField from '../../publish/Field.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    mixins: [
        Row,
        ValidatesFieldConditions,
    ],

    components: { PublishField },

    computed: {
        namePrefix() {
            return `${this.name}[${this.index}]`;
        }
    }

}
</script>
