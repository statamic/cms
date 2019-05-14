<template>
    <div class="page-tree">

        <div class="flex items-center mb-3">
            <slot name="header" />

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
                class="btn btn-primary mr-2"
                @click="openPageSelector"
                v-text="__('Add Page')" />

            <button
                class="btn btn-primary"
                :class="{ 'disabled': !changed }"
                :disabled="!changed"
                @click="save"
                v-text="__('Save Page Order')" />
        </div>

        <loading-graphic v-if="loading"></loading-graphic>

        <div class="tree-node-inner mb-1" v-if="root">
            <tree-branch :root="true" :page="root" :depth="1" />
        </div>

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

            <div v-if="!root" class="tree-node-inner flex mb-1 opacity-50">
                <div class="page-root">
                    <i class="icon icon-home mx-auto opacity-25"></i>
                </div>
                <button class="flex-1 p-1 ml-1 text-grey leading-normal text-left outline-none">
                    No root page defined.
                    <span class="text-blue">Select...</span>
                </button>
            </div>

            <draggable-tree
                draggable
                :data="treeData"
                :space="1"
                :indent="24"
                @change="treeChanged"
            >
                <tree-branch
                    slot-scope="{ data: page, store, vm }"
                    :page="page"
                    :depth="vm.level"
                    @removed="pageRemoved"
                />
            </draggable-tree>

        </div>

        <page-selector
            v-if="pageSelectorOpened"
            :site="site"
            @selected="pagesSelected"
            @closed="closePageSelector"
        />

        <audio ref="soundDrop">
            <source :src="soundDropUrl" type="audio/mp3">
        </audio>

    </div>
</template>


<script>
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
        root: Object,
        site: String,
        localizations: Array,
    },

    data() {
        return {
            loading: false,
            saving: false,
            changed: false,
            pages: this.initialPages,
            treeData: [],
            pageSelectorOpened: false,
        }
    },

    computed: {

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        }

    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
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
            const payload = { pages: this.pages, site: this.site };

            this.$axios.post(this.submitUrl, payload).then(response => {
                this.changed = false;
                this.saving = false;
                this.$notify.success(__('Pages reordered.'));
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
        }

    }

}
</script>
