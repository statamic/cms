<template>

<portal name="replicator-fullscreen" :disabled="!fullScreenMode" :provide="provide">
<!-- These wrappers allow any css that expected the field to
     be within the context of a publish form to continue working
     once it has been portaled out. -->
<div :class="{ 'publish-fields': fullScreenMode }">
<div :class="{ wrapperClasses: fullScreenMode }">
<div class="replicator-fieldtype-container" :class="{'replicator-fullscreen bg-gray-200 dark:bg-dark-700': fullScreenMode }">

    <publish-field-fullscreen-header
        v-if="fullScreenMode"
        :title="config.display"
        :field-actions="fieldActions"
        @close="toggleFullscreen"
    />

    <section :class="{'mt-14 p-4 bg-gray-200 dark:bg-dark-700': fullScreenMode}">

        <sortable-list
            :value="value"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            append-to="body"
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
                    <template v-slot:picker>
                        <add-set-button
                            class="between"
                            :groups="groupConfigs"
                            :sets="setConfigs"
                            :index="index"
                            :enabled="canAddSet"
                            @added="addSet" />
                    </template>
                </replicator-set>
            </div>
        </sortable-list>

        <add-set-button v-if="canAddSet"
            class="mt-3"
            :last="true"
            :groups="groupConfigs"
            :sets="setConfigs"
            :index="value.length"
            :label="config.button_label"
            @added="addSet" />

    </section>

</div>
</div>
</div>
</portal>

</template>

<script>
import uniqid from 'uniqid';
import ReplicatorSet from './Set.vue';
import AddSetButton from './AddSetButton.vue';
import ManagesSetMeta from './ManagesSetMeta';
import { SortableList } from '../../sortable/Sortable';
import reduce from 'underscore/modules/reduce';

export default {

    mixins: [Fieldtype, ManagesSetMeta],

    components: {
        ReplicatorSet,
        SortableList,
        AddSetButton,
    },

    inject: ['storeName'],

    data() {
        return {
            focused: false,
            collapsed: clone(this.meta.collapsed),
            previews: this.meta.previews,
            fullScreenMode: false,
            provide: {
                storeName: this.storeName,
                replicatorSets: this.config.sets
            }
        }
    },

    computed: {

        canAddSet() {
            if (this.isReadOnly) return false;

            return !this.config.max_sets || this.value.length < this.config.max_sets;
        },

        setConfigs() {
            return reduce(this.groupConfigs, (sets, group) => {
                return sets.concat(group.sets);
            }, []);
        },

        groupConfigs() {
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
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return `${__(this.config.display)}: ${__n(':count set|:count sets', this.value.length)}`;
        },

        internalFieldActions() {
            return [
                {
                    title: __('Expand All Sets'),
                    icon: 'arrows-horizontal-expand',
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.expandAll,
                },
                {
                    title: __('Collapse All Sets'),
                    icon: 'arrows-horizontal-collapse',
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.collapseAll,
                },
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => vm.fullScreenMode ? 'shrink-all' : 'expand-bold',
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
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
                ...JSON.parse(JSON.stringify(this.meta.defaults[handle])),
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
                ...JSON.parse(JSON.stringify(old)),
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
            this.previews[id] = previews;
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

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
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

        previews: {
            deep: true,
            handler(value) {
                if (JSON.stringify(this.meta.previews) === JSON.stringify(value)) {
                    return
                }
                const meta = this.meta;
                meta.previews = value;
                this.updateMeta(meta);
            }
        },

    }

}
</script>
