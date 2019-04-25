<template>

    <div class="replicator-fieldtype-container">

        <sortable-list
            v-model="values"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
        >
            <div slot-scope="{}" class="replicator-set-container">
                <replicator-set
                    v-for="(set, index) in values"
                    :key="`set-${set._id}`"
                    :index="index"
                    :values="set"
                    :config="setConfig(set.type)"
                    :parent-name="name"
                    :sortable-item-class="sortableItemClass"
                    :sortable-handle-class="sortableHandleClass"
                    :is-read-only="isReadOnly"
                    @updated="updated"
                    @focus="focused = true"
                    @blur="blurred"
                />
            </div>
        </sortable-list>

        <div class="set-buttons" v-if="!isReadOnly">
            <button
                v-for="set in setConfigs"
                :key="set.handle"
                class="btn mr-1 mb-1"
                @click.prevent="addSet(set.handle)"
            >
                {{ set.display }} <i class="icon icon-plus icon-right"></i>
            </button>
        </div>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import ReplicatorSet from './Set.vue';
import { SortableList } from '../../sortable/Sortable';

export default {

    mixins: [Fieldtype],

    components: {
        ReplicatorSet,
        SortableList
    },

    data() {
        return {
            values: null,
            focused: false,
        }
    },

    computed: {

        setConfigs() {
            return this.config.sets;
        },

        sortableItemClass() {
            return `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return `${this.name}-sortable-handle`;
        }

    },

    created() {
        // Values should be cloned so we don't unintentionally modify the prop.
        let values = JSON.parse(JSON.stringify(this.value || []));

        // Assign each set a unique id that Vue can use as a v-for key.
        this.values = values.map(set => Object.assign(set, { _id: uniqid(), enabled: true }));
    },

    methods: {

        setConfig(handle) {
            return _.find(this.setConfigs, { handle }) || {};
        },

        updated(index, set) {
            this.values.splice(index, 1, set);
        },

        removed(index) {
            if (confirm(__('Are you sure?'))) {
                this.values.splice(index, 1);
            }
        },

        addSet(handle, index) {
            let newSet = {
                _id: uniqid(), // Assign a unique id that Vue can use as a v-for key.
                type: handle,
                enabled: true
            };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            _.each(this.setConfig(handle).fields, field => {
                newSet[field.handle] = field.default
                    // || Statamic.fieldtypeDefaults[field.type] // TODO: inject fieldtype default here.
                    || null;
            });

            if (index === undefined) {
                index = this.values.length;
            }

            this.values.splice(index, 0, newSet);
        },

        collapseAll() { },
        expandAll() { },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        }

    },

    watch: {

        value(value, oldValue) {
            this.values = value;
        },

        values: {
            deep: true,
            handler(values) {
                this.$emit('updated', values);
            }
        },

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        }

    }

}
</script>
