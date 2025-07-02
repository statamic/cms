<template>
    <div>
        <div class="mb-3 flex justify-end items-center">
            <a class="text-2xs text-blue-600 underline me-1.5" v-text="__('Expand All')" @click="expandAll" />
            <span class="text-blue-600 text-2xs">/</span>
            <a
                class="text-2xs text-blue-600 underline ms-1.5"
                v-text="__('Collapse All')"
                @click="collapseAll"
            />
        </div>

        <ui-panel>
            <div class="loading card" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && pages.length == 0" class="no-results flex w-full items-center">
                <slot name="empty" />
            </div>

            <ui-panel-header>
                <div class="page-tree-header font-medium text-sm flex justify-between">
                    <div>Title</div>
                    <div class="me-15">Template</div>
                </div>
            </ui-panel-header>
            <div v-if="!loading" class="page-tree">
                <Draggable
                    ref="tree"
                    v-model="treeData"
                    :disable-drag="!editable"
                    :space="1"
                    :indent="24"
                    :dir="direction"
                    :node-key="(stat) => stat.data.id"
                    :each-droppable="eachDroppable"
                    :root-droppable="rootDroppable"
                    :max-level="maxDepth"
                    :stat-handler="statHandler"
                    @after-drop="treeUpdated"
                    @open:node="nodeOpened"
                    @close:node="nodeClosed"
                >
                    <template #placeholder>
                        <div class="w-full rounded-sm border border-dashed border-blue-400 bg-blue-500/10 p-2">&nbsp;</div>
                    </template>

                    <template #default="{ node, stat }">
                        <tree-branch
                            :ref="`branch-${node.id}`"
                            :page="node"
                            :stat="stat"
                            :depth="stat.level"
                            :first-page-is-root="expectsRoot"
                            :is-open="stat.open"
                            :has-children="stat.children.length > 0"
                            :show-slugs="showSlugs"
                            :show-blueprint="blueprints?.length > 1"
                            :editable="editable"
                            :root="isRoot(stat)"
                            @edit="$emit('edit-page', node, $event)"
                            @toggle-open="stat.open = !stat.open"
                            @removed="pageRemoved"
                            @branch-clicked="$emit('branch-clicked', node)"
                            class="mb-px"
                        >
                            <template #branch-action="props">
                                <slot name="branch-action" v-bind="{ ...props, stat }" />
                            </template>

                            <template #branch-icon="props">
                                <slot name="branch-icon" v-bind="{ ...props, stat }" />
                            </template>

                            <template #branch-options="props">
                                <slot name="branch-options" v-bind="{ ...props, stat }" />
                            </template>
                        </tree-branch>
                    </template>
                </Draggable>
            </div>
        </ui-panel>
    </div>
</template>

<script>
import { dragContext, Draggable, walkTreeData } from '@he-tree/vue';
import TreeBranch from './Branch.vue';
import { PanelHeader, Panel } from '@statamic/ui';

export default {
    components: {
        Draggable,
        TreeBranch,
        PanelHeader,
        Panel,
    },

    props: {
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String },
        submitParameters: { type: Object, default: () => ({}) },
        createUrl: { type: String },
        site: { type: String, required: true },
        localizations: { type: Array },
        maxDepth: { type: Number, default: Infinity },
        expectsRoot: { type: Boolean, required: true },
        showSlugs: { type: Boolean, default: false },
        preferencesPrefix: { type: String },
        editable: { type: Boolean, default: true },
        blueprints: { type: Array },
    },

    data() {
        return {
            loading: false,
            saving: false,
            pages: [],
            treeData: [],
            collapsedState: [],
        };
    },

    computed: {
        activeLocalization() {
            return this.localizations.find((l) => l.active);
        },

        preferencesKey() {
            return this.preferencesPrefix ? `${this.preferencesPrefix}.${this.site}.pagetree` : null;
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    watch: {
        site(site) {
            this.getPages();
        },

        collapsedState: {
            deep: true,
            handler(state) {
                if (this.preferencesKey) {
                    localStorage.setItem(this.preferencesKey, JSON.stringify(state));
                }
            },
        },
    },

    created() {
        this.collapsedState = this.getCollapsedState();

        this.getPages().then(() => {
            this.initialPages = this.pages;
        });

        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },

    methods: {
        isRoot(stat) {
            if (!this.expectsRoot) {
                return false;
            }

            return stat.level === 1 && stat.data.id === this.treeData[0]?.id;
        },

        getPages() {
            this.loading = true;
            const url = `${this.pagesUrl}?site=${this.site}`;

            return this.$axios.get(url).then((response) => {
                this.pages = response.data.pages;
                this.updateTreeData();
                this.loading = false;
            });
        },

        treeUpdated() {
            this.pages = this.$refs.tree.getData();
            this.$emit('changed');
        },

        cleanPagesForSubmission(pages) {
            return pages.map((page) => ({
                id: page.id,
                children: this.cleanPagesForSubmission(page.children),
            }));
        },

        save() {
            if (!this.editable) {
                return;
            }

            this.saving = true;

            const payload = {
                pages: this.cleanPagesForSubmission(this.pages),
                site: this.site,
                expectsRoot: this.expectsRoot,
                ...this.submitParameters,
            };

            return this.$axios
                .patch(this.submitUrl, payload)
                .then((response) => {
                    if (!response.data.saved) {
                        return this.$toast.error(`Couldn't save tree`);
                    }

                    this.$emit('saved', response);
                    this.$toast.success(__('Saved'));
                    this.initialPages = this.pages;
                    return response;
                })
                .catch((e) => {
                    let message = e.response ? e.response.data.message : __('Something went wrong');

                    // For a validation error, show the first message from any field in the toast.
                    if (e.response && e.response.status === 422) {
                        const { errors } = e.response.data;
                        message = errors[Object.keys(errors)[0]][0];
                    }

                    this.$toast.error(message);
                    return Promise.reject(e);
                })
                .finally(() => (this.saving = false));
        },

        addPages(pages, targetParent) {
            this.$refs.tree.addMulti(pages, targetParent);

            this.treeUpdated();
        },

        updateTreeData() {
            this.treeData = [...this.pages];
        },

        pageRemoved(stat, deleteChildren) {
            // If we aren't deleting children, then they need to be moved into
            // the parent. The tree.move() API wasn't working, so instead
            // we're removing the child data and re-adding them as new ones.
            const children = [];
            if (!deleteChildren) stat.children.forEach((child) => children.push({ ...child.data }));

            this.$refs.tree.batchUpdate(() => {
                this.$refs.tree.remove(stat);
                if (children.length) this.$refs.tree.addMulti(children, stat.parent);
            });

            this.treeUpdated();
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (localization.exists) {
                this.editLocalization(localization);
            } else {
                this.createLocalization(localization);
            }
        },

        editLocalization(localization) {
            window.location = localization.url;
        },

        createLocalization(localization) {
            console.log('todo.');
        },

        cancel() {
            if (!confirm(__('Are you sure?'))) return;

            this.pages = this.initialPages;
            this.updateTreeData();
            this.$emit('canceled');
        },

        rootDroppable() {
            if (!this.expectsRoot) {
                return true;
            }

            return dragContext.dragNode.children.length === 0;
        },

        eachDroppable(targetStat) {
            if (!this.expectsRoot) {
                return true;
            }

            return !this.isRoot(targetStat);
        },

        pageUpdated() {
            this.pages = this.$refs.tree.getData();
            this.$emit('changed');
        },

        expandAll() {
            this.$refs.tree.openAll();
        },

        collapseAll() {
            this.$refs.tree.closeAll();
        },

        getNodeByBranchId(id) {
            let branch;
            walkTreeData(this.treeData, (node) => {
                if (node.id === id) {
                    branch = node;
                    return false;
                }
            });
            return branch;
        },

        getCollapsedState() {
            if (!this.preferencesKey) return [];

            return JSON.parse(localStorage.getItem(this.preferencesKey) || '[]');
        },

        nodeOpened(node) {
            this.collapsedState.splice(this.collapsedState.indexOf(node.data.id), 1);
        },

        nodeClosed(node) {
            this.collapsedState.push(node.data.id);
        },

        statHandler(stat) {
            stat.open = !this.collapsedState.includes(stat.data.id);
            return stat;
        },
    },
};
</script>
