<template>

    <div class="bard-set  bg-white border my-4 rounded shadow my-3 whitespace-normal"
        @mousedown="parentMousedown"
        @dragstart="parentDragStart"
    >
        <div class="replicator-set-header">
            <div class="item-move sortable-handle" ref="dragHandle"></div>
            <div class="flex-1 ml-1 flex items-center">
                <label v-text="config.display" class="text-xs"/>
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block replicator-set-instructions" />
            </div>
            <div class="replicator-set-controls">
                <toggle-fieldtype
                    name="set-enabled"
                    class="toggle-sm mr-2"
                    :value="enabled"
                    @updated="enabled = $event" />
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
        'view', // Prosemirror EditorView Object
        'getPos', // function allowing the view to find its position
        'updateAttrs', // function to update attributes defined in `schema`
        'editable', // global editor prop whether the content can be edited
        'options', // array of extension options
        `selected`, // whether its selected
    ],

    components: { SetField },

    inject: ['setConfigs'],

    data() {
        return {
            lastClicked: null,
        }
    },

    computed: {

        values() {
            return this.node.attrs.values;
        },

        config() {
            return _.findWhere(this.setConfigs, { handle: this.values.type });
        },

        enabled: {
            get() {
                return this.node.attrs.enabled;
            },
            set(enabled) {
                return this.updateAttrs({ enabled })
            }
        },

        parentName() {
            return 'todo';
        },

        index() {
            return 0; // todo
        }

    },

    methods: {

        updated(handle, value) {
            let values = Object.assign({}, this.values);
            values.type = this.config.handle;
            values[handle] = value;
            this.updateAttrs({ values });
        },

        showField(field) {
            return true; // todo, send this through fieldconditions mixin
        },

        destroy() {
            let tr = this.view.state.tr;
            let pos = this.getPos();
            tr.delete(pos, pos + this.node.nodeSize);
            this.view.dispatch(tr);
        },

        parentMousedown(e) {
            this.lastClicked = e.target;
        },

        parentDragStart(e) {
            const handle = this.$refs.dragHandle;

            if (this.lastClicked === handle || handle.contains(this.lastClicked)) {
                return;
            }

            e.preventDefault();
        },

    }
}
</script>
