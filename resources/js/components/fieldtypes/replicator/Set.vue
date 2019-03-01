<template>

    <div :class="sortableItemClass" class="replicator-set">

        <div class="replicator-set-header">
            <div class="item-move sortable-handle" :class="sortableHandleClass"></div>
            <div class="replicator-set-title">
                <label v-text="config.display" />
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block" />
            </div>
            <div class="replicator-set-controls">
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
                @updated="updated"
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
import FieldConditions from '../../publish/FieldConditions.js';

export default {

    components: { SetField },

    mixins: [FieldConditions],

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
        }
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

        expand() {
            Vue.set(this.values, '#hidden', false);
        },

        collapse() {
            Vue.set(this.values, '#hidden', true);
        }

    }

}
</script>
