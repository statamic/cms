<template>

    <div>

        <header class="mb-3">
            <breadcrumb v-if="indexUrl" :url="indexUrl" :title="__('CP Nav Preferences')" />

            <div class="flex items-center">
                <h1 class="flex-1">{{ __(title) }}</h1>

                <dropdown-list class="mr-1">
                    <dropdown-item :text="__('Reset Nav Customizations')" class="warning" @click="confirmingReset = true"></dropdown-item>
                </dropdown-list>

                <a @click="discardChanges" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list>
                    <template #trigger>
                        <button class="btn" :class="{ 'flex items-center pr-2': true }">
                            {{ __('Add Item') }}
                            <svg-icon name="chevron-down-xs" class="w-2 ml-2" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="addItem(topLevelTreeData)" />
                    <dropdown-item :text="__('Add Section')" @click="addSection" />
                </dropdown-list>

                <div class="ml-2 text-left" :class="{ 'btn-group': hasSaveAsOptions }">
                    <button
                        class="btn-primary pl-2"
                        :class="{ 'disabled': !changed }"
                        :disabled="!changed"
                        @click="save"
                        v-text="__('Save Changes')" />

                    <dropdown-list v-if="hasSaveAsOptions" class="ml-0">
                        <template #trigger>
                            <button class="btn-primary rounded-l-none flex items-center" :class="{ 'disabled': !changed }">
                                <svg-icon name="chevron-down-xs" class="w-2" />
                            </button>
                        </template>
                        <dropdown-item v-for="option in saveAsOptions" :text="__(option.label)" :key="option.url" @click="saveAs(option.url)" />
                    </dropdown-list>
                </div>
            </div>
        </header>

        <div v-if="!loading" class="page-tree page-tree-with-sections w-full">
            <draggable-tree
                draggable
                cross-tree
                class="mb-4"
                ref="topLevelTree"
                :data="topLevelTreeData"
                :space="1"
                :indent="24"
                @change="changed = true"
                @drag="topLevelTreeDragStart"
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    :disable-sections="true"
                    :top-level="true"
                    @edit="editItem(item, true)"
                    @toggle-open="store.toggleOpen(item)"
                >
                    <template #branch-options="{ item, vm }">
                        <dropdown-item
                            :text="__('Edit')"
                            @click="editingItem = item" />
                        <dropdown-item
                            :text="__('Duplicate')"
                            @click="aliasItem(item)" />
                        <li class="divider"></li>
                        <dropdown-item
                            v-if="itemIsVisible(item)"
                            :text="isHideable(item) ? __('Hide') : __('Remove')"
                            class="warning"
                            @click="isHideable(item) ? hideItem(item) : removeItem(item, vm)" />
                        <dropdown-item
                            v-else
                            :text="__('Show')"
                            @click="showItem(item)" />
                    </template>
                </tree-branch>
            </draggable-tree>


            <draggable-tree
                draggable
                cross-tree
                class="page-tree-with-sections"
                ref="mainTree"
                :data="mainTreeData"
                :space="1"
                :indent="24"
                @change="changed = true"
                @drag="mainTreeDragStart"
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    @edit="editItem(item)"
                    @toggle-open="store.toggleOpen(item)"
                >
                    <template #branch-options="{ item }">
                        <dropdown-item
                            :text="__('Add Item')"
                            @click="addItem(item.children)" />
                        <dropdown-item
                            :text="__('Edit')"
                            @click="editItem(item)" />
                        <dropdown-item
                            v-if="! isSectionNode(item)"
                            :text="__('Pin to Top Level')"
                            @click="pinItem(item)" />
                        <dropdown-item
                            v-if="! isSectionNode(item)"
                            :text="__('Duplicate')"
                            @click="aliasItem(item)" />
                        <li class="divider"></li>
                        <dropdown-item
                            v-if="itemIsVisible(item)"
                            :text="isHideable(item) ? __('Hide') : __('Remove')"
                            class="warning"
                            @click="isHideable(item) ? hideItem(item) : removeItem(item)" />
                        <dropdown-item
                            v-else
                            :text="__('Show')"
                            @click="showItem(item)" />
                    </template>
                </tree-branch>
            </draggable-tree>
        </div>

        <item-editor
            v-if="creatingItem"
            :creating="true"
            @closed="resetItemEditor"
            @updated="itemAdded"
        />

        <item-editor
            v-if="editingItem"
            :item="editingItem"
            @closed="resetItemEditor"
            @updated="itemUpdated"
        />

        <section-editor
            v-if="creatingSection"
            :creating="true"
            @closed="resetSectionEditor"
            @updated="sectionAdded"
        />

        <section-editor
            v-if="editingSection"
            :section-item="editingSection"
            @closed="resetSectionEditor"
            @updated="sectionUpdated"
        />

        <confirmation-modal
            v-if="confirmingReset === true"
            :title="__('Reset')"
            :bodyText="__('Are you sure you want to reset nav customizations?')"
            :buttonText="__('Reset')"
            :danger="true"
            @confirm="reset"
            @cancel="confirmingReset = false"
        >
        </confirmation-modal>

    </div>

</template>

<script>
import {DraggableTree} from 'vue-draggable-nested-tree/dist/vue-draggable-nested-tree';
import TreeBranch from './Branch.vue';
import ItemEditor from './ItemEditor.vue';
import SectionEditor from './SectionEditor.vue';
import { data_get } from  '../../bootstrap/globals.js'

export default {

    components: {
        DraggableTree,
        TreeBranch,
        ItemEditor,
        SectionEditor,
    },

    props: {
        title: {
            type: String,
            require: true,
        },
        nav: {
            type: Array,
            required: true,
        },
        indexUrl: {
            type: String,
        },
        updateUrl: {
            type: String,
            require: true,
        },
        destroyUrl: {
            type: String,
            require: true,
        },
        saveAsOptions: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            initialNav: clone(this.nav),
            loading: false,
            topLevelTreeData: [],
            mainTreeData: [],
            changed: false,
            targetDataArray: null,
            creatingItem: false,
            editingItem: false,
            creatingSection: false,
            editingSection: false,
            confirmingReset: false,
        }
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    mounted() {
        this.setInitialNav(this.nav);
    },

    computed: {

        isDirty() {
            return this.changed;
        },

        hasSaveAsOptions() {
            return this.saveAsOptions.length;
        },

    },

    methods: {

        setInitialNav(nav) {
            let navConfig = clone(nav);
            let topLevelConfig = navConfig.shift();

            this.topLevelTreeData = _.chain(topLevelConfig.items)
                .map((section) => this.normalizeNavConfig(section))
                .values()
                .value();

            this.mainTreeData = _.chain(navConfig)
                .mapObject((section) => this.normalizeNavConfig(section))
                .values()
                .value();
        },

        discardChanges() {
            this.setInitialNav(this.initialNav);

            this.changed = false;
        },

        normalizeNavConfig(config) {
            let item = {
                text: config.display,
                config: config,
                manipulations: config.manipulations || {},
            };

            if (config.items) {
                item.children = config.items.map(childItem => {
                    return {
                        text: childItem.display,
                        children: childItem.children.map(childChildItem => this.normalizeNavConfig(childChildItem)),
                        open: false,
                        config: childItem,
                        manipulations: childItem.manipulations || {},
                    };
                });
            }

            return item;
        },

        topLevelTreeDragStart(node) {
            let nodeDepth = 1;

            this.traverseTree(node, (_, { depth }) => {
                nodeDepth = Math.max(nodeDepth, depth);
            });

            // Hardcode max depth of 2 (nav items, and one level of nav item children)
            const maxDepth = 2 - nodeDepth;

            // Ensure max depth
            this.traverseTree(this.topLevelTreeData, (childNode, { depth }) => {
                if (childNode !== node) {
                    this.$set(childNode, 'droppable', depth <= maxDepth);
                }
            });
        },

        mainTreeDragStart(node) {
            let nodeDepth = 1;

            this.traverseTree(node, (_, { depth }) => {
                nodeDepth = Math.max(nodeDepth, depth);
            });

            // Hardcode max depth of 3 (sections, nav items, and one level of nav item children)
            const maxDepth = 3 - nodeDepth;

            // Ensure you can only drop nav item nodes into top level tree
            this.$set(this.$refs.topLevelTree.rootData, 'droppable', ! this.isSectionNode(node));

            // Ensure you can only drop section nodes to main tree root
            this.$set(this.$refs.mainTree.rootData, 'droppable', this.isSectionNode(node));

            // Ensure nav item nodes can only be dropped within section nodes
            this.traverseTree(this.mainTreeData, (childNode, { depth }) => {
                if (childNode !== node) {
                    this.$set(childNode, 'droppable', depth <= maxDepth && ! this.isSectionNode(node));
                }
            });
        },

        isSectionNode(node) {
            return node.parent.isRoot === true;
        },

        traverseTree(nodes, callback, parentPath = []) {
            const nodesArray = Array.isArray(nodes) ? nodes : [nodes];

            nodesArray.every((node, index) => {
                const nodePath = [...parentPath, index];
                const path = nodePath.join('.');
                const depth = nodePath.length;

                if (false === callback(node, { path, depth, index })) {
                    return false;
                }

                if (node.children.length) {
                    this.traverseTree(node.children, callback, nodePath);
                }

                return true;
            });
        },

        addItem(targetDataArray) {
            this.targetDataArray = targetDataArray;
            this.creatingItem = true;
        },

        addSection() {
            this.creatingSection = true;
        },

        itemAdded(createdConfig) {
            let item = this.normalizeNavConfig(createdConfig);

            item.manipulations = {
                action: '@create',
                display: createdConfig.display,
                url: createdConfig.url,
            };

            this.targetDataArray.push(item);
            this.resetItemEditor();
            this.changed = true;
        },

        sectionAdded(sectionDisplay) {
            let item = this.normalizeNavConfig({
                display: sectionDisplay,
                display_original: false,
            });

            this.mainTreeData.push(item);
            this.resetSectionEditor();
            this.changed = true;
        },

        editItem(item, topLevel) {
            if (this.isSectionNode(item) && ! topLevel) {
                this.editingSection = item;
            } else {
                this.editingItem = item;
            }
        },

        itemUpdated(updatedConfig, item) {
            item.text = updatedConfig.display;

            item.manipulations = {
                action: data_get(item.manipulations, 'action', '@modify'),
                display: updatedConfig.display,
                display_original: updatedConfig.display_origial,
                url: updatedConfig.url,
            };

            this.resetItemEditor();
            this.changed = true;
        },

        sectionUpdated(sectionDisplay, sectionItem) {
            sectionItem.text = sectionDisplay;

            this.resetSectionEditor();
            this.changed = true;
        },

        resetItemEditor() {
            this.editingItem = false;
            this.creatingItem = false;
            this.targetDataArray = false;
        },

        resetSectionEditor() {
            this.editingSection = false;
            this.creatingSection = false;
        },

        pinItem(item) {
            this.aliasItem(item, this.topLevelTreeData);
        },

        aliasItem(item, treeData) {
            let newItem = this.normalizeNavConfig(clone(item.config));

            newItem.manipulations = { action: '@alias' };

            let tree = treeData || item.parent.children;

            tree.push(newItem);

            this.changed = true;
        },

        itemIsVisible(item) {
            return item.manipulations.action !== '@remove';
        },

        isHideable(item) {
            let action = data_get(item.manipulations, 'action');

            return ! ['@alias', '@move', '@create'].includes(action);
        },

        removeItem(item) {
            item._vm.store.deleteNode(item);

            this.changed = true;
        },

        hideItem(item) {
            item.trashedManipulations = item.manipulations;

            item.manipulations = { action: '@remove' };

            this.changed = true;
        },

        showItem(item) {
            Vue.delete(item.manipulations, 'action');

            if (item.trashedManipulations) {
                item.manipulations = item.trashedManipulations;
            }

            this.changed = true;
        },

        reset() {
            this.$axios
                .delete(this.destroyUrl)
                .then(() => window.location.reload())
                .catch(() => this.$toast.error(__('Something went wrong')));
        },

        save() {
            if (! this.changed) {
                return;
            }

            this.saveAs(this.updateUrl);
        },

        saveAs(url) {
            let tree = this.preparePreferencesSubmission();

            this.$axios
                .patch(url, {tree})
                .then(() => location.reload())
                .catch(() => this.$toast.error(__('Something went wrong')));
        },

        preparePreferencesSubmission() {
            let tree = [];

            tree.push({
                'display': 'Top Level',
                'display_original': 'Top Level',
                'action': false,
                'items': this.prepareItemsForSubmission(this.topLevelTreeData),
            });

            this.mainTreeData.forEach(section => {
                tree.push({
                    'display': section.text,
                    'display_original': section.config.display_original || section.text,
                    'action': section.manipulations.action || false,
                    'items': this.prepareItemsForSubmission(section.children),
                });
            });

            return tree;
        },

        prepareItemsForSubmission(treeItems) {
            let items = [];

            treeItems.forEach(item => {
                items.push({
                    'id': this.prepareItemIdForSubmission(item),
                    'manipulations': item.manipulations,
                    'children': item.children ? this.prepareItemsForSubmission(item.children) : [],
                });
            });

            return items;
        },

        prepareItemIdForSubmission(item) {
            return data_get(item, 'config.id')
                ? item.config.id.replace('::clone', '')
                : item.text.toLowerCase().replace(' ', '_');
        },

    },

}
</script>
