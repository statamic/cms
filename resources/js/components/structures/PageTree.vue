<template>
    <div>

        <div class="mb-1 flex justify-end">
            <a
                class="text-2xs text-blue mr-2 underline"
                v-text="__('Expand All')"
                @click="expandAll"
            />
            <a
                class="text-2xs text-blue mr-1 underline"
                v-text="__('Collapse All')"
                @click="collapseAll"
            />
        </div>

        <div class="loading card" v-if="loading">
            <loading-graphic />
        </div>

        <div v-if="!loading && pages.length == 0" class="no-results w-full flex items-center">
            <slot name="empty" />
        </div>

        <div v-if="!loading" class="page-tree w-full">
            <draggable-tree
                draggable
                ref="tree"
                :data="treeData"
                :space="1"
                :indent="24"
                @change="treeChanged"
                @drag="treeDragstart"
                @nodeOpenChanged="saveTreeState"
            >
                <tree-branch
                    slot-scope="{ data: page, store, vm }"
                    :page="page"
                    :depth="vm.level"
                    :vm="vm"
                    :first-page-is-root="expectsRoot"
                    :has-collection="hasCollection"
                    :is-open="page.open"
                    :has-children="page.children.length > 0"
                    :show-slugs="showSlugs"
                    @edit="$emit('edit-page', page, vm, store, $event)"
                    @toggle-open="store.toggleOpen(page)"
                    @removed="pageRemoved"
                    @children-orphaned="childrenOrphaned"
                >
                    <template #branch-icon="props">
                        <slot name="branch-icon" v-bind="{ ...props, vm }" />
                    </template>

                    <template #branch-options="props">
                        <slot name="branch-options" v-bind="{ ...props, vm }" />
                    </template>
                </tree-branch>
            </draggable-tree>

        </div>

    </div>
</template>


<script>
import * as th from 'tree-helper';
import {DraggableTree} from 'vue-draggable-nested-tree/dist/vue-draggable-nested-tree';
import TreeBranch from './Branch.vue';

export default {

    components: {
        DraggableTree,
        TreeBranch,
    },

    props: {
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        submitParameters: { type: Object, default: () => ({}) },
        createUrl: { type: String },
        site: { type: String, required: true },
        localizations: { type: Array },
        maxDepth: { type: Number, default: Infinity, },
        expectsRoot: { type: Boolean, required: true },
        showSlugs: { type: Boolean, default: false },
        hasCollection: { type: Boolean, required: true },
        preferencesPrefix: { type: String },
    },

    data() {
        return {
            loading: false,
            saving: false,
            pages: [],
            treeData: [],
        }
    },

    computed: {

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        },

        preferencesKey() {
            return this.preferencesPrefix ? `${this.preferencesPrefix}.${this.site}.pagetree` : null;
        },

    },

    watch: {

        site(site) {
            this.getPages();
        }
    },

    created() {
        this.getPages().then(() => {
            this.initialPages = this.pages;
        })

        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    methods: {

        getPages() {
            this.loading = true;
            const url = `${this.pagesUrl}?site=${this.site}`;

            return this.$axios.get(url).then(response => {
                this.pages = response.data.pages;
                this.loadTreeState(this.pages);
                this.updateTreeData();
                this.loading = false;
            });
        },

        treeChanged(node, tree) {
            if (!this.validate()) {
                this.updateTreeData();
                return;
            }

            this.treeUpdated(tree);
        },

        treeUpdated(tree) {
            tree = tree || this.$refs.tree;

            this.pages = tree.getPureData();
            this.$emit('changed');
        },

        validate() {
            let isValid = true;

            this.traverseTree(this.treeData, (node, { isRoot }) => {
                if (isRoot && node.children.length) {
                    isValid = false;
                    return false;
                }
            });

            return isValid;
        },

        cleanPagesForSubmission(pages) {
            return _.map(pages, page => ({
                id: page.id,
                children: this.cleanPagesForSubmission(page.children)
            }));
        },

        save() {
            this.saving = true;

            const payload = {
                pages: this.cleanPagesForSubmission(this.pages),
                site: this.site,
                expectsRoot: this.expectsRoot,
                ...this.submitParameters
            };

            return this.$axios.patch(this.submitUrl, payload).then(response => {
                this.$emit('saved', response);
                this.$toast.success(__('Saved'));
                this.initialPages = this.pages;
                this.saveTreeState();
                return response;
            }).catch(e => {
                let message = e.response ? e.response.data.message : __('Something went wrong');

                // For a validation error, show the first message from any field in the toast.
                if (e.response && e.response.status === 422) {
                    const { errors } = e.response.data;
                    message = errors[Object.keys(errors)[0]][0];
                }

                this.$toast.error(message);
                return Promise.reject(e);
            }).finally(() => this.saving = false);
        },

        addPages(pages, targetParent) {
            const parent = targetParent
                ? targetParent.data.children
                : this.treeData;

            pages.forEach(selection => {
                parent.push({
                    id: selection.id,
                    entry: selection.entry,
                    title: selection.title,
                    entry_title: selection.entry_title,
                    slug: selection.slug,
                    url: selection.url,
                    edit_url: selection.edit_url,
                    status: selection.status,
                    children: []
                });
            });

            this.treeUpdated();
        },

        updateTreeData() {
            this.treeData = clone(this.pages);
        },

        pageRemoved(tree) {
            this.pages = tree.getPureData();
            this.$emit('changed');
        },

        childrenOrphaned(tree) {
            this.pages = tree.getPureData();
            this.$emit('changed');
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
            if (! confirm(__('Are you sure?'))) return;

            this.pages = this.initialPages;
            this.updateTreeData();
            this.$emit('canceled');
        },

        treeDragstart(node) {
            let nodeDepth = 1;
            this.traverseTree(node, (_, { depth }) => {
                nodeDepth = Math.max(nodeDepth, depth);
            });
            const maxDepth = this.maxDepth - nodeDepth;

            this.traverseTree(this.treeData, (childNode, { depth, isRoot }) => {
                if (childNode !== node) {
                    this.$set(childNode, 'droppable', !isRoot && depth <= maxDepth);
                }
            });
        },

        pageUpdated(tree) {
            this.pages = tree.getPureData();
            this.$emit('changed');
        },

        expandAll() {
            this.traverseTree(this.treeData, node => {
                node.open = true
            })
            this.saveTreeState();
        },

        collapseAll() {
            this.traverseTree(this.treeData, node => {
                node.open = false
            })
            this.saveTreeState();
        },

        loadTreeState(treeData) {
            if (! this.preferencesKey) {
                return;
            }

            const closed = JSON.parse(localStorage.getItem(this.preferencesKey) || '[]');
            this.applyTreeState(closed, treeData);
        },

        saveTreeState() {
            if (! this.preferencesKey) {
                return;
            }

            const closed = this.getTreeState(this.treeData);
            return localStorage.setItem(this.preferencesKey, JSON.stringify(closed));
        },

        getTreeState(nodes) {
            const closed = [];

            this.traverseTree(nodes, (node, { path }) => {
                if (node.children.length && ! node.open) {
                    closed.push(path);
                }
            });

            return closed;
        },

        applyTreeState(closed, nodes) {
            this.traverseTree(nodes, (node, { path }) => {
                if (node.children.length) {
                    node.open = ! closed.includes(path);
                }
            });
        },

        traverseTree(nodes, callback, parentPath = []) {
            const nodesArray = Array.isArray(nodes) ? nodes : [nodes];
            nodesArray.every((node, index) => {
                const nodePath = [...parentPath, index];
                const path = nodePath.join('.');
                const depth = nodePath.length;
                const isRoot = this.expectsRoot && depth === 1 && index === 0;

                if (false === callback(node, { path, depth, index, isRoot })) {
                    return false;
                }

                if (node.children.length) {
                    this.traverseTree(node.children, callback, nodePath);
                }

                return true;
            });
        },

        getNodeByBranchId(id) {
            let branch;

            th.breadthFirstSearch(this.treeData, (node) => {
                if (node.id === id) {
                    branch = node
                    return false
                }
            });

            return branch;
        }

    }
}
</script>
