<template>
    <portal name="replicator-fullscreen" :disabled="!fullScreenMode" :provide="provide">
        <!-- These wrappers allow any css that expected the field to
     be within the context of a publish form to continue working
     once it has been portaled out. -->
        <div :class="{ 'publish-fields': fullScreenMode }">
            <div :class="{ wrapperClasses: fullScreenMode }">
                <div
                    class="replicator-fieldtype-container"
                    :class="{ 'replicator-fullscreen fixed inset-0 min-h-screen overflow-scroll rounded-none bg-gray-100 dark:bg-gray-800': fullScreenMode }"
                >
                    <publish-field-fullscreen-header
                        v-if="fullScreenMode"
                        :title="config.display"
                        :field-actions="fieldActions"
                        @close="toggleFullscreen"
                    />

                    <section :class="{ 'mt-12 p-4': fullScreenMode }">
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
                                    :has-error="setHasError(set._id)"
                                    :show-field-previews="config.previews"
                                    @collapsed="collapseSet(set._id)"
                                    @expanded="expandSet(set._id)"
                                    @duplicated="duplicateSet(set._id)"
                                    @removed="removed(set, index)"
                                >
                                    <template v-slot:picker>
                                        <add-set-button
                                            variant="between"
                                            :groups="groupConfigs"
                                            :sets="setConfigs"
                                            :index="index"
                                            :enabled="canAddSet"
                                            :is-first="index === 0"
                                            :loading-set="loadingSet"
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
                            :show-connector="value.length > 0"
                            :index="value.length"
                            :label="config.button_label"
                            :is-first="value.length === 0"
                            :loading-set="loadingSet"
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
import { nanoid as uniqid } from 'nanoid';
import ReplicatorSet from './Set.vue';
import AddSetButton from './AddSetButton.vue';
import ManagesSetMeta from './ManagesSetMeta';
import { SortableList } from '../../sortable/Sortable';
import { data_get } from "@/bootstrap/globals.js";

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
            escBinding: null,
            provide: {
                replicatorSets: this.config.sets,
                showReplicatorFieldPreviews: this.config.previews,
            },
            errorsById: {},
            setsCache: {},
            loadingSet: null,
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
            if (!this.showFieldPreviews) return;

            return `${__(this.config.display)}: ${__n(':count set|:count sets', this.value.length)}`;
        },

        internalFieldActions() {
            return [
                {
                    title: __('Expand All Sets'),
                    icon: 'expand',
                    quick: true,
                    disabled: () => this.collapsed.length === 0,
                    visible: this.config.collapse !== 'accordion',
                    visibleWhenReadOnly: true,
                    run: this.expandAll,
                },
                {
                    title: __('Collapse All Sets'),
                    icon: 'collapse',
                    quick: true,
                    disabled: () =>this.collapsed.length === this.value.length,
                    visibleWhenReadOnly: true,
                    run: this.collapseAll,
                },
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.fullScreenMode ? 'fullscreen-close' : 'fullscreen-open'),
                    quick: true,
                    visible: this.config.fullscreen,
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
            this.loadingSet = handle;

            this.fetchSet(handle)
                .then(data => this._addSet(handle, index, data))
                .catch(() => this.$toast.error(__('Something went wrong')))
                .finally(() => this.loadingSet = null);
        },

        _addSet(handle, index, data) {
            const set = {
                ...JSON.parse(JSON.stringify(data.defaults)),
                _id: uniqid(),
                type: handle,
                enabled: true,
            };

            this.updateSetMeta(set._id, data.new);

            this.$nextTick(() => {
                this.update([...this.value.slice(0, index), set, ...this.value.slice(index)]);
                this.expandSet(set._id);
            });
        },

        async fetchSet(set) {
            return new Promise(async (resolve, reject) => {
                const field = this.replicatorFieldPath();
                const setCacheKey = `${field}.${set}`;
                const reference = this.publishContainer.reference;
                const blueprint = this.publishContainer.blueprint.fqh;

                if (this.setsCache[setCacheKey]) {
                    resolve(this.setsCache[setCacheKey]);
                    return;
                }

                this.$axios.post(cp_url('fieldtypes/replicator/set'), { blueprint, reference, field, set })
                    .then(response => {
                        this.setsCache[setCacheKey] = response.data;
                        resolve(response.data);
                    })
                    .catch(error => reject(error));
            });
        },

        /**
         * Returns the path to the Replicator field, replacing any set indexes with handles.
         */
        replicatorFieldPath() {
            if (!this.fieldPathPrefix) {
                return this.handle;
            }

            return this.fieldPathKeys
                .map((key, index) => {
					if (['attrs', 'values'].includes(key)) return;

                    if (Number.isInteger(parseInt(key))) {
	                    let setValues =  data_get(this.publishContainer.values, this.fieldPathKeys.slice(0, index + 1).join('.'));

	                    return setValues.attrs?.values.type || setValues.type;
                    }

                    return key;
                })
                .filter((key) => key !== undefined)
                .concat(this.handle)
                .join('.');
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

            if (this.fullScreenMode) {
                this.escBinding = this.$keys.bindGlobal('esc', this.toggleFullscreen);
            } else {
                if (this.escBinding) {
                    this.escBinding.destroy();
                    this.escBinding = null;
                }
            }
        },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        },

        setHasError(id) {
            if (Object.keys(this.errorsById).length === 0) {
                return false;
            }

            return this.errorsById.hasOwnProperty(id) && this.errorsById[id].length > 0;
        },
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

        loadingSet(loading) {
            this.$progress.loading('replicator-set', !!loading);
        },

        'publishContainer.errors': {
            immediate: true,
            handler(errors) {
                this.errorsById = Object.entries(errors).reduce((acc, [key, value]) => {
                    if (!key.startsWith(this.setFieldPathPrefix)) {
                        return acc;
                    }

                    const subKey = key.replace(`${this.setFieldPathPrefix}.`, '');
                    const setIndex = subKey.split('.').shift();
                    const setId = this.value[setIndex]?._id;

                    if (setId) {
                        acc[setId] = value;
                    }

                    return acc;
                }, {});
            },
        },
    },
};
</script>
