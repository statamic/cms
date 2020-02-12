<template>
    <div class="page-tree">

        <header class="mb-3">
            <breadcrumb :url="breadcrumbUrl" :title="__('Structures')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <dropdown-item :text="__('Edit Structure Config')" :redirect="editUrl" />
                </dropdown-list>

                <a @click="cancel" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list>
                    <template #trigger>
                        <button class="btn" v-text="`${__('Add Link')}`" />
                    </template>
                    <dropdown-item :text="__('Link to URL')" @click="linkPage" />
                    <dropdown-item :text="__('Link to Entry')" @click="linkToEntries" />
                </dropdown-list>

                <create-entry-button
                    v-if="hasCollection"
                    class="ml-2"
                    :url="createEntryUrl()"
                    :blueprints="collectionBlueprints"
                    :text="__('Create Page')" />

                <button
                    class="btn btn-primary ml-2"
                    :class="{ 'disabled': !changed }"
                    :disabled="!changed"
                    @click="save"
                    v-text="__('Save Changes')" />
            </div>
        </header>

        <loading-graphic v-if="loading"></loading-graphic>

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

        <div v-if="pages.length == 0" class="no-results border-dashed border-2 w-full flex items-center">
            <div class="text-center max-w-md mx-auto rounded-lg px-4 py-4">
                <slot name="no-pages-svg" />
                <h1 class="my-3" v-text="__('Create your first link now')" />
                <p class="text-grey mb-3">
                    {{ __('messages.structures_empty') }}
                </p>
                <button v-if="!hasCollection" class="btn btn-primary btn-lg" v-text="__('Create first page')" @click="openPageCreator" />

                <create-entry-button
                    v-if="hasCollection"
                    button-class="btn btn-primary"
                    :url="createEntryUrl()"
                    :blueprints="collectionBlueprints"
                    :text="__('Create first page')" />
            </div>
        </div>

        <div class="page-tree w-full" v-show="pages.length">
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
                    slot-scope="{ data: page, store: tree, vm }"
                    :page="page"
                    :depth="vm.level"
                    :vm="vm"
                    :first-page-is-root="expectsRoot"
                    :hasCollection="hasCollection"
                    @edit="editPage(page, vm)"
                    @updated="pageUpdated(tree)"
                    @removed="pageRemoved"
                    @link-page="linkChildPage(vm)"
                    @link-entries="linkChildEntries(vm)"
                    @create-entry="createEntry"
                />
            </draggable-tree>

        </div>

        <page-selector
            v-if="collections.length"
            ref="selector"
            :site="site"
            :collections="collections"
            :exclusions="exclusions"
            @selected="pagesSelected"
        />

        <page-editor
            v-if="creatingPage"
            @closed="closePageCreator"
            @submitted="pageCreated"
        />

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
import PageSelector from './PageSelector.vue';
import PageEditor from './PageEditor.vue';

export default {

    components: {
        DraggableTree,
        TreeBranch,
        PageSelector,
        PageEditor,
    },

    props: {
        title: String,
        breadcrumbUrl: String,
        initialPages: Array,
        pagesUrl: String,
        submitUrl: String,
        editUrl: String,
        createUrl: String,
        soundDropUrl: String,
        site: String,
        localizations: Array,
        collections: Array,
        maxDepth: {
            type: Number,
            default: Infinity,
        },
        expectsRoot: Boolean,
        hasCollection: Boolean,
        collectionBlueprints: Array,
    },

    data() {
        return {
            loading: false,
            saving: false,
            changed: false,
            pages: this.initialPages,
            treeData: [],
            pageIds: [],
            parentPageForAdding: null,
            targetPage: null,
            creatingPage: false,
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

        expectsRoot(value) {
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

        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
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
            const payload = { pages: this.pages, site: this.site, expectsRoot: this.expectsRoot };

            this.$axios.post(this.submitUrl, payload).then(response => {
                this.changed = false;
                this.saving = false;
                this.$toast.success(__('Saved'));
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
                const isRoot = this.expectsRoot && level === 1 && index === 0;
                const isBeyondMaxDepth = level > childNodeMaxLevel;
                let droppable = true;
                if (isRoot || isBeyondMaxDepth) droppable = false;
                this.$set(childNode, 'droppable', droppable);
            });
        },

        createEntry(parent) {
            window.location = this.createEntryUrl(parent);
        },

        createEntryUrl(parent) {
            let url = this.createUrl;
            if (parent) url += '?parent=' + parent;
            return url;
        },

        linkChildPage(vm) {
            this.parentPageForAdding = vm;
            this.openPageCreator();
        },

        linkChildEntries(vm) {
            this.parentPageForAdding = vm;
            this.linkToEntries();
        },

        linkPage() {
            this.openPageCreator();
        },

        linkToEntries() {
            this.$refs.selector.linkExistingItem();
        },

        openPageCreator() {
            this.creatingPage = true;
        },

        closePageCreator() {
            this.creatingPage = false;
        },

        pageCreated(page) {
            this.closePageCreator();
            this.pagesSelected([{
                title: page.title,
                url: page.url,
                children: []
            }]);
        },

        pageUpdated(tree) {
            this.pages = tree.getPureData();
            this.changed = true;
        },

        makeFirstPage() {
            if (this.hasCollection) {
                return window.location = this.createUrl
            } else {
                this.openPageCreator()
            }
        }

    }

}
</script>
