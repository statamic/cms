<template>
    <portal name="replicator-fullscreen" :disabled="!fullScreenMode" :provide="provide">
        <!-- These wrappers allow any css that expected the field to
     be within the context of a publish form to continue working
     once it has been portaled out. -->
        <div :class="{ 'publish-fields': fullScreenMode }">
            <div :class="{ wrapperClasses: fullScreenMode }">
                <div
                    class="replicator-fieldtype-container"
                    :class="{ 'replicator-fullscreen dark:bg-dark-700 bg-gray-200': fullScreenMode }"
                >
                    <publish-field-fullscreen-header
                        v-if="fullScreenMode"
                        :title="config.display"
                        :field-actions="fieldActions"
                        @close="toggleFullscreen"
                    />

                    <section :class="{ 'dark:bg-dark-700 mt-14 bg-gray-200 p-4': fullScreenMode }">
                        <sortable-list
                            :model-value="value"
                            :vertical="true"
                            :item-class="sortableItemClass"
                            :handle-class="sortableHandleClass"
                            append-to="body"
                            constrain-dimensions
                            @update:model-value="sorted($event)"
                            @dragstart="$emit('focus')"
                            @dragend="$emit('blur')"
                            v-slot="{}"
                        >
                            <div class="relative">
                                <ReplicatorSet
                                    v-for="(set, index) in value"
                                    :key="set._id"
                                    :id="set._id"
                                    :index
                                    :field-path="setFieldPathPrefix"
                                    :meta-path="setMetaPathPrefix"
                                    :values="set"
                                    :config="setConfig(set.type)"
                                    :sortable-item-class="sortableItemClass"
                                    :sortable-handle-class="sortableHandleClass"
                                    :collapsed="collapsed.includes(set._id)"
                                    :enabled="set.enabled"
                                    :read-only
                                    :can-add-set="canAddSet"
                                    :has-error="setHasError(index)"
                                    :show-field-previews="config.previews"
                                    @collapsed="collapseSet(set._id)"
                                    @expanded="expandSet(set._id)"
                                    @duplicated="duplicateSet(set._id)"
                                    @removed="removed(set, index)"
                                >
                                    <template v-slot:picker>
                                        <add-set-button
                                            variant="between"
                                            v-if="index !== 0"
                                            :groups="groupConfigs"
                                            :sets="setConfigs"
                                            :index="index"
                                            :enabled="canAddSet"
                                            @added="addSet"
                                        />
                                    </template>
                                </ReplicatorSet>
                            </div>
                        </sortable-list>

                        <add-set-button
                            v-if="canAddSet"
                            :groups="groupConfigs"
                            :sets="setConfigs"
                            :show-connector="false"
                            :index="value.length"
                            :label="config.button_label"
                            @added="addSet"
                        />
                    </section>
                </div>
            </div>
        </div>
    </portal>
</template>

<script>
import Fieldtype from '../Fieldtype.vue';
import uniqid from 'uniqid';
import ReplicatorSet from './Set.vue';
import AddSetButton from './AddSetButton.vue';
import ManagesSetMeta from './ManagesSetMeta';
import { SortableList } from '../../sortable/Sortable';

export default {
    mixins: [Fieldtype, ManagesSetMeta],

    components: {
        ReplicatorSet,
        SortableList,
        AddSetButton,
    },

    data() {
        return {
            focused: false,
            collapsed: clone(this.meta.collapsed),
            fullScreenMode: false,
            provide: {
                replicatorSets: this.config.sets,
            },
        };
    },

    computed: {
        setFieldPathPrefix() {
            return this.fieldPathPrefix ? `${this.fieldPathPrefix}.${this.handle}` : this.handle;
        },

        setMetaPathPrefix() {
            return this.metaPathPrefix ? `${this.metaPathPrefix}.${this.handle}` : this.handle;
        },

        canAddSet() {
            if (this.isReadOnly) return false;

            return !this.config.max_sets || this.value.length < this.config.max_sets;
        },

        setConfigs() {
            return this.groupConfigs.reduce((sets, group) => {
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

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

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
                    icon: ({ vm }) => (vm.fullScreenMode ? 'shrink-all' : 'expand-bold'),
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
    },

    methods: {
        setConfig(handle) {
            return this.setConfigs.find((c) => c.handle === handle) || {};
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

            this.updateSetMeta(set._id, this.meta.new[handle]);

            this.update([...this.value.slice(0, index), set, ...this.value.slice(index)]);

            this.expandSet(set._id);
        },

        duplicateSet(old_id) {
            const index = this.value.findIndex((v) => v._id === old_id);
            const old = this.value[index];
            const set = {
                ...JSON.parse(JSON.stringify(old)),
                _id: uniqid(),
            };

            this.updateSetMeta(set._id, this.meta.existing[old_id]);

            this.update([...this.value.slice(0, index + 1), set, ...this.value.slice(index + 1)]);

            this.expandSet(set._id);
        },

        collapseSet(id) {
            if (!this.collapsed.includes(id)) {
                this.collapsed.push(id);
            }
        },

        expandSet(id) {
            if (this.config.collapse === 'accordion') {
                this.collapsed = this.value.map((v) => v._id).filter((v) => v !== id);
                return;
            }

            if (this.collapsed.includes(id)) {
                var index = this.collapsed.indexOf(id);
                this.collapsed.splice(index, 1);
            }
        },

        collapseAll() {
            this.collapsed = this.value.map((v) => v._id);
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

            return Object.keys(this.publishContainer.errors ?? []).some((handle) => handle.startsWith(prefix));
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
    },
};
</script>
