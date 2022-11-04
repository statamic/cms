<template>

    <div class="replicator-fieldtype-container">

        <div class="absolute top-0 right-0 p-3 text-2xs" v-if="config.collapse !== 'accordion' && value.length > 0">
            <button @click="collapseAll" class="text-blue hover:text-black mr-1" v-text="__('Collapse All')" />
            <button @click="expandAll" class="text-blue hover:text-black" v-text="__('Expand All')" />
        </div>

        <sortable-list
            :value="value"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            constrain-dimensions
            @input="sorted($event)"
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
        >
            <div slot-scope="{}" class="replicator-set-container">
                <replicator-set
                    v-for="(set, index) in value"
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
                    :field-path-prefix="fieldPathPrefix || handle"
                    :has-error="setHasError(index)"
                    :previews="previews[set._id]"
                    :show-field-previews="config.previews"
                    :can-add-set="canAddSet"
                    @collapsed="collapseSet(set._id)"
                    @expanded="expandSet(set._id)"
                    @duplicated="duplicateSet(set._id)"
                    @updated="updated"
                    @meta-updated="updateSetMeta(set._id, $event)"
                    @removed="removed(set, index)"
                    @focus="focused = true"
                    @blur="blurred"
                    @previews-updated="updateSetPreviews(set._id, $event)"
                >
                    <template v-slot:picker v-if="canAddSet">
                        <set-picker
                            class="replicator-set-picker-between"
                            :sets="setConfigs"
                            :index="index"
                            @added="addSet" />
                    </template>
                </replicator-set>
            </div>
        </sortable-list>

        <set-picker v-if="canAddSet"
            :last="true"
            :sets="setConfigs"
            :index="value.length"
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

    inject: ['storeName'],

    data() {
        return {
            focused: false,
            collapsed: clone(this.meta.collapsed),
        }
    },

    computed: {

        previews() {
            return this.meta.previews;
        },

        canAddSet() {
            if (this.isReadOnly) return false;

            return !this.config.max_sets || this.value.length < this.config.max_sets;
        },

        setConfigs() {
            return this.config.sets;
        },

        sortableItemClass() {
            return `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return `${this.name}-sortable-handle`;
        },

        storeState() {
            return this.$store.state.publish[this.storeName] || {};
        },

        replicatorPreview() {
            return `${this.config.display}: ${__n(':count set|:count sets', this.value.length)}`;
        }
    },

    methods: {

        setConfig(handle) {
            return _.find(this.setConfigs, { handle }) || {};
        },

        updated(index, set) {
            this.update([...this.value.slice(0, index), set, ...this.value.slice(index + 1)]);
        },

        removed(set, index) {
            this.removeSetMeta(set._id);

            this.update([...this.value.slice(0, index), ...this.value.slice(index + 1)]);
        },

        sorted(value) {
            this.update(value);
        },

        addSet(handle, index) {
            const set = {
                ...this.meta.defaults[handle],
                _id: uniqid(),
                type: handle,
                enabled: true,
            };

            this.updateSetPreviews(set._id, {});

            this.updateSetMeta(set._id, this.meta.new[handle]);

            this.update([
                ...this.value.slice(0, index),
                set,
                ...this.value.slice(index)
            ]);

            this.expandSet(set._id);
        },

        duplicateSet(old_id) {
            const index = this.value.findIndex(v => v._id === old_id);
            const old = this.value[index];
            const set = {
                ...old,
                _id: uniqid(),
            };

            this.updateSetPreviews(set._id, {});

            this.updateSetMeta(set._id, this.meta.existing[old_id]);

            this.update([
                ...this.value.slice(0, index + 1),
                set,
                ...this.value.slice(index + 1)
            ]);

            this.expandSet(set._id);
        },

        updateSetPreviews(id, previews) {
            this.updateMeta({
                ...this.meta,
                previews: {
                    ...this.meta.previews,
                    [id]: previews,
                },
            });
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
            this.collapsed = _.pluck(this.value, '_id');
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

        setHasError(index) {
            const prefix = `${this.fieldPathPrefix || this.handle}.${index}.`;

            return Object.keys(this.storeState.errors ?? []).some(handle => handle.startsWith(prefix));
        },
    },

    mounted() {
        if (this.config.collapse) this.collapseAll();
    },

    watch: {

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        },

        collapsed(collapsed) {
            this.updateMeta({ ...this.meta, collapsed: clone(collapsed) });
        },

    }

}
</script>
