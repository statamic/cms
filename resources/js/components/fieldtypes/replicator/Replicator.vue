<template>

    <div class="replicator-fieldtype-container">

        <div class="absolute pin-t pin-r p-3 text-2xs" v-if="values.length > 0">
            <button @click="collapseAll" class="text-blue hover:text-black mr-1" v-text="__('Collapse All')" />
            <button @click="expandAll" class="text-blue hover:text-black" v-text="__('Expand All')" />
        </div>

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
                    :meta="metas[set._id]"
                    :config="setConfig(set.type)"
                    :parent-name="name"
                    :sortable-item-class="sortableItemClass"
                    :sortable-handle-class="sortableHandleClass"
                    :is-read-only="isReadOnly"
                    :collapsed="collapsed.includes(set._id)"
                    @collapsed="collapseSet(set._id)"
                    @expanded="expandSet(set._id)"
                    @updated="updated"
                    @removed="removed(set, index)"
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
            metas: {},
            focused: false,
            collapsed: [],
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

        let metas = {};

        values = values.map((set, index) => {
            let id = uniqid();
            metas[id] = this.meta.existing[index];
            return Object.assign(set, { _id: id, enabled: true });
        });

        this.values = values;
        this.metas = metas;
    },

    methods: {

        setConfig(handle) {
            return _.find(this.setConfigs, { handle }) || {};
        },

        updated(index, set) {
            let oldValues = clone(this.values);
            let newValues = clone(this.values);

            newValues.splice(index, 1, set);

            if (JSON.stringify(oldValues) !== JSON.stringify(newValues)) {
                this.values = newValues;
            }
        },

        removed(set, index) {
            Vue.delete(this.metas, set._id);
            this.values.splice(index, 1);
        },

        addSet(handle, index) {
            const id = uniqid();

            let set = Object.assign({}, this.meta.defaults[handle], {
                _id: id,
                type: handle,
                enabled: true,
            });

            this.metas[id] = this.meta.new[handle];
            this.values.push(set);
        },

        collapseSet(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id)
            }
        },

        expandSet(id) {
            if (this.collapsed.includes(id)) {
                var index = this.collapsed.indexOf(id);
                this.collapsed.splice(index, 1);
            }
        },

        collapseAll() {
            this.collapsed = _.pluck(this.values, '_id');
        },

        expandAll() {
            this.collapsed = [];
        },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        }

    },

    watch: {

        value(value) {
            this.values = value;
        },

        values: {
            deep: true,
            handler(values) {
                this.update(values);
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
