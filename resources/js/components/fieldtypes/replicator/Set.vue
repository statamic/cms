<template>

    <div :class="sortableItemClass" class="replicator-set">

        <div class="replicator-set-header" :class="{ 'p-1': isReadOnly }">
            <div class="item-move sortable-handle" :class="sortableHandleClass" v-if="!isReadOnly"></div>
            <div class="flex-1 ml-1 flex items-center">
                <label v-text="config.display" class="text-xs"/>
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block replicator-set-instructions" />
            </div>
            <div class="replicator-set-controls" v-if="!isReadOnly">
                <toggle-fieldtype name="set-enabled" class="toggle-sm mr-2" @updated="toggleEnabledState" :value="values.enabled" />
                <dropdown-list>
                    <ul class="dropdown-menu">
                        <li class="warning"><a @click.prevent="destroy">{{ __('Delete Set') }}</a></li>
                    </ul>
                </dropdown-list>
            </div>
        </div>

        <div class="replicator-set-body">
            <set-field
                v-for="field in fields"
                v-show="showField(field)"
                :key="field.handle"
                :field="field"
                :value="values[field.handle]"
                :parent-name="parentName"
                :set-index="index"
                :read-only="isReadOnly"
                @updated="updated(field.handle, $event)"
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
import SetField from './Field.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    components: { SetField },

    mixins: [ValidatesFieldConditions],

    props: {
        config: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        values: {
            type: Object,
            required: true
        },
        parentName: {
            type: String,
            required: true
        },
        sortableItemClass: {
            type: String
        },
        sortableHandleClass: {
            type: String
        },
        isReadOnly: Boolean,
    },

    computed: {

        fields() {
            return this.config.fields;
        },

        display() {
            return this.config.display || this.values.type;
        },

        instructions() {
            return this.config.instructions ? markdown(this.config.instructions) : null;
        },

        hasMultipleFields() {
            return this.fields.length > 1;
        },

        isHidden() {
            return this.values['#hidden'] === true;
        }

    },

    methods: {

        updated(handle, value) {
            let set = JSON.parse(JSON.stringify(this.values));
            set[handle] = value;
            this.$emit('updated', this.index, set);
        },

        destroy() {
            this.$emit('removed', this.index);
        },

        toggle() {
            this.isHidden ? this.expand() : this.collapse();
        },

        toggleEnabledState() {
            Vue.set(this.values, 'enabled', ! this.values.enabled);
        },

        expand() {
            Vue.set(this.values, '#hidden', false);
        },

        collapse() {
            Vue.set(this.values, '#hidden', true);
        }

    }

}
</script>
