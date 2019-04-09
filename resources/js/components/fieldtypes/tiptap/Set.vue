<template>

    <div class="bard-set  bg-white border my-4 rounded shadow my-3 whitespace-normal">
        <div class="replicator-set-header">
            <div class="item-move sortable-handle"></div>
            <div class="flex-1 ml-1 flex items-center">
                <label v-text="config.display" class="text-xs"/>
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block replicator-set-instructions" />
            </div>
            <div class="replicator-set-controls">
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
                v-for="field in config.fields"
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

<script>
import SetField from '../replicator/Field.vue';

export default {

    props: [
        'node', // Prosemirror Node Object
        'updateAttrs', // function to update attributes defined in `schema`
        'editable', // global editor prop whether the content can be edited
        'options', // array of extension options
        `selected`, // whether its selected
    ],

    components: { SetField },

    inject: ['setConfigs'],

    computed: {

        values() {
            return this.node.attrs.values;
        },

        config() {
            return _.findWhere(this.setConfigs, { handle: this.values.type });
        },

        parentName() {
            return 'todo';
        },

        index() {
            return 0; // todo
        }

    },

    methods: {

        toggleEnabledState() {
            // todo
        },

        updated(handle, value) {
            let values = Object.assign({}, this.values);
            values.type = this.config.handle;
            values[handle] = value;
            this.updateAttrs({ values });
        },

        showField(field) {
            return true; // todo, send this through fieldconditions mixin
        }

    }
}
</script>
