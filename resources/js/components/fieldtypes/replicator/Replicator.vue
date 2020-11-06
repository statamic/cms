<template>

    <div class="replicator-fieldtype-container">

        <div class="absolute top-0 right-0 p-3 text-2xs" v-if="config.collapse !== 'accordion' && values.length > 0">
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
                    :key="set._id"
                    :index="index"
                    :values="set"
                    :meta="meta.existing[set._id]"
                    :config="setConfig(set.type)"
                    :parent-name="name"
                    :sortable-item-class="sortableItemClass"
                    :sortable-handle-class="sortableHandleClass"
                    :is-read-only="isReadOnly"
                    :collapsed="collapsed.includes(set._id)"
                    :error-key-prefix="errorKeyPrefix || handle"
                    :previews="previews[set._id]"
                    @collapsed="collapseSet(set._id)"
                    @expanded="expandSet(set._id)"
                    @updated="updated"
                    @meta-updated="updateSetMeta(set._id, $event)"
                    @removed="removed(set, index)"
                    @focus="focused = true"
                    @blur="blurred"
                    @previews-updated="previews[set._id] = $event"
                >
                    <template v-slot:picker v-if="!isReadOnly && index !== values.length-1">
                        <set-picker
                            class="replicator-set-picker-between"
                            :sets="setConfigs"
                            :index="index"
                            @added="addSet" />
                    </template>
                </replicator-set>
            </div>
        </sortable-list>

        <set-picker v-if="!isReadOnly"
            :last="true"
            :sets="setConfigs"
            :index="values.length"
            @added="addSet" />

    </div>

</template>

<script>
import uniqid from 'uniqid';
import ReplicatorSet from './Set.vue';
import SetPicker from './SetPicker.vue';
import ManagesSetMeta from './ManagesSetMeta';
import { SortableList } from '../../sortable/Sortable';

export default {

    mixins: [Fieldtype, ManagesSetMeta],

    components: {
        ReplicatorSet,
        SortableList,
        SetPicker,
    },

    data() {
        return {
            values: this.value,
            focused: false,
            collapsed: this.meta.collapsed,
            previews: this.meta.previews,
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
            this.removeSetMeta(set._id);
            this.values.splice(index, 1);
        },

        addSet(handle, index) {
            let set = Object.assign({}, this.meta.defaults[handle], {
                _id: `set-${uniqid()}`,
                type: handle,
                enabled: true,
            });

            let previews = {};
            Object.keys(this.meta.defaults[handle]).forEach(key => previews[key] = null);
            this.previews = Object.assign({}, this.previews, { [set._id]: previews });

            this.updateSetMeta(set._id, this.meta.new[handle]);

            this.values.splice(index, 0, set);

            this.expandSet(set._id);
        },

        collapseSet(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id)
            }
        },

        expandSet(id) {
            if (this.config.collapse === 'accordion') {
                this.collapsed = this.value.map(v => v._id).filter(v => v !== id);
                return;
            }

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
        },

    },

    mounted() {
        if (this.config.collapse) this.collapseAll();
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
        },

        collapsed(value) {
            const meta = this.meta;
            meta.collapsed = value;
            this.updateMeta(meta);
        },

        previews(previews) {
            let meta = this.meta;
            meta.previews = previews;
            this.updateMeta(meta);
        }

    }

}
</script>
