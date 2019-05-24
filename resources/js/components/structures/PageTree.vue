<template>
    <div class="page-tree">

        <div class="flex items-center mb-3">
            <slot name="header" />

            <div class="pt-px text-2xs text-grey-60 mr-2" v-if="isDirty" v-text="__('Unsaved Changes')" />

            <dropdown-list class="mr-2">
                <ul class="dropdown-menu">
                    <li><a :href="editUrl">{{ __('Edit Structure') }}</a></li>
                    <li class="warning"><a href="#">{{ __('Delete Structure') }}</a></li>
                </ul>
            </dropdown-list>

            <v-select
                v-if="localizations.length > 1"
                :value="activeLocalization"
                label="name"
                :clearable="false"
                :options="localizations"
                :searchable="false"
                :multiple="false"
                @input="localizationSelected"
                class="w-48 mr-2"
            >
                <template slot="option" slot-scope="option">
                    <div class="flex items-center">
                        <span class="little-dot mr-1" :class="{ 'bg-green': option.exists, 'bg-red': !option.exists }" />
                        {{ option.name }}
                        <svg-icon name="check" class="h-3 w-3 ml-sm text-grey" v-if="option.active" />
                    </div>
                </template>
            </v-select>

            <button
                class="btn mr-2"
                @click="openPageSelector"
                v-text="__('Add Page')" />

            <button
                class="btn mr-2"
                :class="{ 'disabled': !changed }"
                :disabled="!changed"
                @click="cancel"
                v-text="__('Cancel')" />

            <button
                class="btn btn-primary"
                :class="{ 'disabled': !changed }"
                :disabled="!changed"
                @click="save"
                v-text="__('Save')" />
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

        <div v-if="pages.length">

            <div class="flex items-center mb-2">
                <toggle-fieldtype v-model="firstPageIsRoot" name="firstPageIsRoot" />
                <div class="text-xs ml-1">First page is the root.</div>
            </div>

            <draggable-tree
                draggable
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
                />
            </draggable-tree>

        </div>

        <page-selector
            v-if="pageSelectorOpened"
            :site="site"
            :collections="collections"
            @selected="pagesSelected"
            @closed="closePageSelector"
        />

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
    },

    data() {
        return {
            loading: false,
            saving: false,
            changed: false,
            pages: this.initialPages,
            treeData: [],
            pageSelectorOpened: false,
            firstPageIsRoot: this.hasRoot,
        }
    },

    computed: {

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        },

        isDirty() {
            return this.$dirty.has('page-tree');
        }

    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
        },

        firstPageIsRoot(value) {
            this.changed = true;
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

        treeChanged(node, tree) {
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

        openPageSelector() {
            this.pageSelectorOpened = true;
        },

        closePageSelector() {
            this.pageSelectorOpened = false;
        },

        pagesSelected(selections) {
            this.closePageSelector();

            selections.forEach(selection => {
                this.pages.push({
                    id: selection.id,
                    title: selection.title,
                    slug: selection.slug,
                    url: selection.url,
                    edit_url: selection.edit_url,
                    children: []
                });
            });

            this.updateTreeData();
            this.changed = true;
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
        }

    }

}
</script>
