<template>
    <div class="page-tree">

        <div class="flex items-center mb-3">
            <slot name="header" />
            <div class="pt-px text-2xs text-grey-60 mr-2" v-if="isDirty" v-text="__('Unsaved Changes')" />
        </div>

        <loading-graphic v-if="loading"></loading-graphic>

        <div v-if="pages.length == 0">
            <div class="no-results border-dashed border-2">
                <div class="text-center max-w-md mx-auto mt-5 rounded-lg px-4 py-8">
                    <slot name="no-pages-svg" />
                    <h1 class="my-3">Add the first page now</h1>
                    <p class="text-grey mb-3">
                        {{ __('Structures can contain entries arranged into a heirarchy from which you can create URLs or navigation areas.') }}
                    </p>
                    <button class="btn btn-primary btn-lg" v-text="__('Add first page')" @click="openPageSelector" />
                </div>
            </div>
        </div>

        <div v-if="pages.length" class="flex flex-row-reverse justify-between">

            <div class="publish-sidebar">
                <div class="publish-section">
                    <div class="p-2">
                        <button
                            class="btn btn-primary w-full mb-2"
                            :class="{ 'disabled': !changed }"
                            :disabled="!changed"
                            @click="save"
                            v-text="__('Save')" />

                        <div class="flex flex-wrap justify-center text-grey text-2xs">

                            <a :href="editUrl"
                                class="flex items-center m-1 whitespace-no-wrap"
                                :class="{ 'disabled': !changed }"
                                @click="cancel">
                                <svg-icon name="hammer-wrench" class="w-4 mr-sm" />
                                {{ __('Edit') }}
                            </a>

                            <button
                                class="flex items-center m-1 whitespace-no-wrap outline-none"
                                :class="{ 'opacity-50': !changed }"
                                @click="cancel">
                                <span class="mr-sm">&times;</span>
                                {{ __('Discard Changes') }}
                            </button>
                        </div>
                    </div>

                    <div class="" v-if="localizations.length > 1">
                        <div
                            v-for="option in localizations"
                            :key="option.handle"
                            class="revision-item flex items-center border-grey-30"
                            :class="{ 'opacity-50': !option.active }"
                            @click="localizationSelected(option)"
                        >
                            <div class="flex-1 flex items-center">
                                <span class="little-dot mr-1 bg-green" />
                                {{ option.name }}
                            </div>
                            <div class="badge bg-orange" v-if="option.origin" v-text="__('Origin')" />
                            <div class="badge bg-blue" v-if="option.active" v-text="__('Active')" />
                            <div class="badge bg-purple" v-if="option.root && !option.origin && !option.active" v-text="__('Root')" />
                        </div>
                    </div>

                    <div class="publish-fields">
                        <page-selector
                            v-if="collections.length"
                            ref="selector"
                            :site="site"
                            :collections="collections"
                            :exclusions="exclusions"
                            @selected="pagesSelected"
                        />

                        <form-group
                            v-if="hasCollection"
                            fieldtype="toggle"
                            handle="root"
                            display="Home Page"
                            :instructions='__(`The first page in the tree will be the home page.`)'
                            v-model="firstPageIsRoot" />
                    </div>
                </div>
            </div>

            <div class="page-tree w-full">
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
                        :first-page-is-root="firstPageIsRoot"
                        @removed="pageRemoved"
                        @add-page="addChildPage(vm)"
                    />
                </draggable-tree>

            </div>

        </div>

        <audio ref="soundDrop">
            <source :src="soundDropUrl" type="audio/mp3">
        </audio>

    </div>
</template>


<script>
import * as th from 'tree-helper';
import {Sortable, Plugins} from '@shopify/draggable';
import {DraggableTree} from 'vue-draggable-nested-tree';
import TreeBranch from './Branch.vue';
import PageSelector from './PageSelector.vue';

export default {

    components: {
        DraggableTree,
        TreeBranch,
        PageSelector,
    },

    props: {
        initialPages: Array,
        pagesUrl: String,
        submitUrl: String,
        editUrl: String,
        soundDropUrl: String,
        site: String,
        localizations: Array,
        collections: Array,
        maxDepth: {
            type: Number,
            default: Infinity,
        },
        hasRoot: Boolean,
        hasCollection: Boolean,
    },

    data() {
        return {
            loading: false,
            saving: false,
            changed: false,
            pages: this.initialPages,
            treeData: [],
            pageIds: [],
            firstPageIsRoot: this.hasRoot,
            parentPageForAdding: null,
        }
    },

    computed: {

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        },

        isDirty() {
            return this.$dirty.has('page-tree');
        },

        exclusions() {
            return this.hasCollection ? this.pageIds : [];
        }

    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
        },

        firstPageIsRoot(value) {
            this.changed = true;
        },

        pages: {
            immediate: true,
            deep: true,
            handler(pages) {
                this.pageIds = this.getPageIds(pages);
            }
        }

    },

    created() {
        this.updateTreeData();
    },

    methods: {

        getPages() {
            this.loading = true;
            const url = this.pagesUrl;

            this.$axios.get(url).then(response => {
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
            this.changed = true;
        },

        save() {
            this.saving = true;
            const payload = { pages: this.pages, site: this.site, firstPageIsRoot: this.firstPageIsRoot };

            this.$axios.post(this.submitUrl, payload).then(response => {
                this.changed = false;
                this.saving = false;
                this.$notify.success(__('Saved'));
            });
        },

        pagesSelected(selections) {
            const parent = this.parentPageForAdding
                ? this.parentPageForAdding.data.children
                : this.treeData;

            selections.forEach(selection => {
                parent.push({
                    id: selection.id,
                    title: selection.title,
                    slug: selection.slug,
                    url: selection.url,
                    edit_url: selection.edit_url,
                    children: []
                });
            });

            this.parentPageForAdding = null;
            this.treeUpdated();
        },

        updateTreeData() {
            this.treeData = clone(this.pages);
        },

        pageRemoved(tree) {
            this.pages = tree.getPureData();
            this.changed = true;
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
            if (!this.isDirty) return;
            if (! confirm('Are you sure?')) return;

            this.pages = this.initialPages;
            this.updateTreeData();
            this.changed = false;
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
                const isRoot = this.firstPageIsRoot && level === 1 && index === 0;
                const isBeyondMaxDepth = level > childNodeMaxLevel;
                let droppable = true;
                if (isRoot || isBeyondMaxDepth) droppable = false;
                this.$set(childNode, 'droppable', droppable);
            });
        },

        addChildPage(vm) {
            this.parentPageForAdding = vm;
            this.$refs.selector.linkExistingItem();
        }

    }

}
</script>
