<template>
    <div>

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
            >
                <tree-branch
                    slot-scope="{ data: page, store, vm }"
                    :page="page"
                    :depth="vm.level"
                    :vm="vm"
                    :first-page-is-root="expectsRoot"
                    :hasCollection="hasCollection"
                    @edit="$emit('edit-page', page, vm, store, $event)"
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

        <audio ref="soundDrop">
            <source :src="soundDropUrl" type="audio/mp3">
        </audio>

    </div>
</template>


<script>
import * as th from 'tree-helper';
import {Sortable, Plugins} from '@shopify/draggable';
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
        hasCollection: { type: Boolean, required: true },
    },

    data() {
        return {
            loading: false,
            saving: false,
            pages: [],
            treeData: [],
            pageIds: [],
            soundDropUrl: this.$config.get('resourceUrl') + '/audio/click.mp3',
        }
    },

    computed: {

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        },

        exclusions() {
            return this.hasCollection ? this.pageIds : [];
        }

    },

    watch: {

        pages: {
            deep: true,
            handler(pages) {
                this.pageIds = this.getPageIds(pages);
            }
        },

        pageIds(ids) {
            this.$emit('page-ids-updated', ids);
        },

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
                this.updateTreeData();
                this.loading = false;
            });
        },

        getPageIds(pages) {
            let ids = [];
            pages.forEach(page => {
                ids.push(page.id);
                if (page.children.length) {
                    ids = [...ids, ...this.getPageIds(page.children)];
                }
            })
            return ids;
        },

        treeChanged(node, tree) {
            this.treeUpdated(tree);
        },

        treeUpdated(tree) {
            tree = tree || this.$refs.tree;

            this.pages = tree.getPureData();
            this.$refs.soundDrop.play();
            this.$emit('changed');
        },

        save() {
            this.saving = true;
            const payload = { pages: this.pages, site: this.site, expectsRoot: this.expectsRoot, ...this.submitParameters };

            return this.$axios.post(this.submitUrl, payload).then(response => {
                this.$emit('saved');
                this.$toast.success(__('Saved'));
                this.initialPages = this.pages;
                return response;
            }).catch(e => {
                this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'));
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
                    title: selection.title,
                    slug: selection.slug,
                    url: selection.url,
                    edit_url: selection.edit_url,
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
            // Support for maxDepth.
            // Adapted from https://github.com/phphe/vue-draggable-nested-tree/blob/a5bcf2ccdb4c2da5a699bf2ddf3443f4e1dba8f9/src/examples/MaxLevel.vue#L56-L75
            let nodeLevels = 1;
            th.depthFirstSearch(node, (childNode) => {
                if (childNode._vm.level > nodeLevels) {
                    nodeLevels = childNode._vm.level;
                }
            });
            nodeLevels = nodeLevels - node._vm.level + 1;
            const childNodeMaxLevel = this.maxDepth - nodeLevels;
            th.depthFirstSearch(this.treeData, (childNode) => {
                if (childNode === node) return;
                const index = childNode.parent.children.indexOf(childNode);
                const level = childNode._vm.level;
                const isRoot = this.expectsRoot && level === 1 && index === 0;
                const isBeyondMaxDepth = level > childNodeMaxLevel;
                let droppable = true;
                if (isRoot || isBeyondMaxDepth) droppable = false;
                this.$set(childNode, 'droppable', droppable);
            });
        },

        pageUpdated(tree) {
            this.pages = tree.getPureData();
            this.$emit('changed');
        }

    }

}
</script>
