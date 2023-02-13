<template>

    <div>

        <header class="mb-6">
            <breadcrumb v-if="indexUrl" :url="indexUrl" :title="__('CP Nav Preferences')" />

            <div class="flex items-center">
                <h1 class="flex-1">{{ title }}</h1>

                <dropdown-list class="mr-2">
                    <dropdown-item :text="__('Reset Nav Customizations')" class="warning" @click="confirmingReset = true"></dropdown-item>
                </dropdown-list>

                <a @click="discardChanges" class="text-2xs text-blue mr-4 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list>
                    <template #trigger>
                        <button class="btn flex items-center pr-4">
                            {{ __('Add Item') }}
                            <svg-icon name="chevron-down-xs" class="w-2 ml-4" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="addItem(topLevelTreeData)" />
                    <dropdown-item :text="__('Add Tab')" @click="addTab" />
                </dropdown-list>

                <div class="ml-4 text-left" :class="{ 'btn-group': hasSaveAsOptions }">
                    <button
                        class="btn-primary pl-4"
                        :class="{ 'disabled': !changed }"
                        :disabled="!changed"
                        @click="save"
                        v-text="__('Save Changes')" />

                    <dropdown-list v-if="hasSaveAsOptions" class="ml-0">
                        <template #trigger>
                            <button class="btn-primary rounded-l-none flex items-center">
                                <svg-icon name="chevron-down-xs" class="w-2" />
                            </button>
                        </template>
                        <h6 class="p-2">{{ __('Save to') }}...</h6>
                        <dropdown-item v-for="option in saveAsOptions" :key="option.url" @click="saveAs(option.url)" class="group">
                            <div class="flex items-start pr-4">
                                <svg-icon :name="option.icon" class="text-gray shrink-0 mr-2 w-4 group-hover:text-white" />
                                <span class="whitespace-normal">{{ option.label }}</span>
                            </div>
                        </dropdown-item>
                    </dropdown-list>
                </div>
            </div>
        </header>

        <div class="mb-2 flex justify-end">
            <a
                class="text-2xs text-blue mr-4 underline"
                v-text="__('Expand All')"
                @click="expandAll"
            />
            <a
                class="text-2xs text-blue mr-2 underline"
                v-text="__('Collapse All')"
                @click="collapseAll"
            />
        </div>

        <div v-if="!loading" class="page-tree page-tree-with-tabs w-full">
            <draggable-tree
                draggable
                cross-tree
                class="mb-8"
                :class="{ 'tab-placeholder-inner': showTopLevelTabPlaceholder }"
                ref="topLevelTree"
                :data="topLevelTreeData"
                :space="1"
                :indent="24"
                @change="changed = true"
                @drag="treeDrag"
                @drop="treeDrop"
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    :disable-tabs="true"
                    :top-level="true"
                    @edit="editItem(item, true)"
                    @toggle-open="store.toggleOpen(item)"
                >
                    <template #branch-options="{ item }">
                        <dropdown-item
                            v-if="vm.level < 2"
                            :text="__('Add Item')"
                            @click="addItem(item.children)" />
                        <dropdown-item
                            :text="__('Edit')"
                            @click="editingItem = item" />
                        <dropdown-item
                            :text="__('Duplicate')"
                            @click="aliasItem(item)" />
                        <li class="divider" />
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
                class="page-tree-with-tabs"
                ref="mainTree"
                :data="mainTreeData"
                :space="1"
                :indent="24"
                @change="changed = true"
                @drag="treeDrag"
                @drop="treeDrop"
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :parent-tab="getParentTabNode(item)"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    @edit="editItem(item)"
                    @toggle-open="store.toggleOpen(item)"
                >
                    <template #branch-options="{ item }">
                        <dropdown-item
                            v-if="vm.level < 3"
                            :text="__('Add Item')"
                            @click="addItem(item.children)" />
                        <dropdown-item
                            :text="__('Edit')"
                            @click="editItem(item)" />
                        <dropdown-item
                            v-if="! isTabNode(item)"
                            :text="__('Pin to Top Level')"
                            @click="pinItem(item)" />
                        <dropdown-item
                            v-if="! isTabNode(item)"
                            :text="__('Duplicate')"
                            @click="aliasItem(item)" />
                        <li class="divider" />
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

        <tab-editor
            v-if="creatingTab"
            :creating="true"
            @closed="resetTabEditor"
            @updated="tabAdded"
        />

        <tab-editor
            v-if="editingTab"
            :tab-item="editingTab"
            @closed="resetTabEditor"
            @updated="tabUpdated"
        />

        <confirmation-modal
            v-if="confirmingReset"
            :title="__('Reset')"
            :bodyText="__('Are you sure you want to reset nav customizations?')"
            :buttonText="__('Reset')"
            :danger="true"
            @confirm="reset"
            @cancel="confirmingReset = false"
        />

        <confirmation-modal
            v-if="confirmingRemoval"
            :title="__('Remove')"
            :bodyText="__('Are you sure you want to remove this tab and all of its children?')"
            :buttonText="__('Remove')"
            :danger="true"
            @confirm="removeItem(confirmingRemoval, true)"
            @cancel="confirmingReset = false"
        />

    </div>

</template>

<script>
import {DraggableTree} from 'vue-draggable-nested-tree/dist/vue-draggable-nested-tree';
import TreeBranch from './Branch.vue';
import ItemEditor from './ItemEditor.vue';
import TabEditor from './TabEditor.vue';
import { data_get } from  '../../bootstrap/globals.js'

export default {

    components: {
        DraggableTree,
        TreeBranch,
        ItemEditor,
        TabEditor,
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
            originalTabItems: {},
            changed: false,
            targetDataArray: null,
            creatingItem: false,
            editingItem: false,
            creatingTab: false,
            editingTab: false,
            confirmingReset: false,
            confirmingRemoval: false,
            draggingNode: false,
            draggingNodeParent: false,
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

        showTopLevelTabPlaceholder() {
            if (! this.topLevelTreeData.length) {
                return true;
            }

            return this.draggingNode
                && this.topLevelTreeData.length === 1
                && this.topLevelTreeData[0]._id === this.draggingNode._id;
        },

    },

    methods: {

        setInitialNav(nav) {
            let navConfig = clone(nav);

            this.setOriginalTabItems(navConfig);

            let topLevelConfig = navConfig.shift();

            this.topLevelTreeData = _.chain(topLevelConfig.items)
                .map(tab => this.normalizeNavConfig(tab, false))
                .values()
                .value();

            this.mainTreeData = _.chain(navConfig)
                .mapObject(tab => this.normalizeNavConfig(tab))
                .values()
                .value();
        },

        setOriginalTabItems(nav) {
            nav.forEach(tab => this.originalTabItems[tab.display_original] = tab.items_original || []);
        },

        discardChanges() {
            this.setInitialNav(this.initialNav);

            this.changed = false;
        },

        normalizeNavConfig(config, isTabNode = true) {
            let item = {
                text: config.display,
                config: config,
                original: config.original,
                manipulations: isTabNode ? config : config.manipulations || {},
                isTab: isTabNode,
                open: isTabNode,
            };

            let children = config.items || config.children;

            if (children) {
                item.children = children.map(childItem => {
                    return {
                        text: childItem.display,
                        children: childItem.children.map(childChildItem => this.normalizeNavConfig(childChildItem, false)),
                        open: false,
                        config: childItem,
                        original: childItem.original,
                        manipulations: childItem.manipulations || {},
                        isTab: false,
                    };
                });
            }

            return item;
        },

        treeDrag(node) {
            this.draggingNode = node;
            this.draggingNodeParent = node.parent;

            let nodeDepth = 1;

            this.traverseTree(node, (_, { depth }) => {
                nodeDepth = Math.max(nodeDepth, depth);
            });

            // Ensure you can only drop nav item nodes into top level tree root
            this.$set(this.$refs.topLevelTree.rootData, 'droppable', ! this.isTabNode(node));

            // Ensure you can only drop tab nodes to main tree root
            this.$set(this.$refs.mainTree.rootData, 'droppable', this.isTabNode(node));

            // Hardcode max depths
            const topLevelTreeMaxDepth = 2 - nodeDepth; // 2 for nav items, and one level of nav item children
            const mainTreeMaxDepth = 3 - nodeDepth; // 3 for tabs, nav items, and one level of nav item children

            // Ensure max depth for top level tree
            this.traverseTree(this.topLevelTreeData, (childNode, { depth }) => {
                if (childNode !== node) {
                    this.$set(childNode, 'droppable', depth <= topLevelTreeMaxDepth && ! this.isTabNode(node));
                }
            });

            // Ensure max depth for main tree
            this.traverseTree(this.mainTreeData, (childNode, { depth }) => {
                if (childNode !== node) {
                    this.$set(childNode, 'droppable', depth <= mainTreeMaxDepth && ! this.isTabNode(node));
                }
            });
        },

        treeDrop(node) {
            this.updateItemAction(node);

            if (data_get(this.draggingNodeParent, 'isRoot') !== true) {
                this.updateItemAction(this.draggingNodeParent);
            }

            this.$nextTick(() => {
                this.draggingNode = false;
                this.draggingNodeParent = false;
            });
        },

        isTabNode(node) {
            return data_get(node, 'isTab', false);
        },

        isCustomTabNode(node) {
            return this.isTabNode(node) && data_get(node, 'manipulations.action') === '@create';
        },

        getParentTabNode(node) {
            if (! this.isTabNode(node) && node !== undefined) {
                return this.getParentTabNode(node.parent);
            }

            return node;
        },

        isChildItemNode(node) {
            if (data_get(node, 'parent.isRoot')) {
                return false;
            }

            return ! this.isTabNode(node.parent);
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

        addTab() {
            this.creatingTab = true;
        },

        itemAdded(createdConfig) {
            let item = this.normalizeNavConfig(createdConfig, false);

            item.manipulations = {
                action: '@create',
                display: createdConfig.display,
                url: createdConfig.url,
            };

            this.targetDataArray.push(item);
            this.resetItemEditor();
            this.changed = true;
        },

        tabAdded(tabDisplay) {
            let item = this.normalizeNavConfig({
                action: '@create',
                display: tabDisplay,
                display_original: false,
            });

            this.mainTreeData.push(item);
            this.resetTabEditor();
            this.changed = true;
        },

        editItem(item, topLevel) {
            if (this.isTabNode(item) && ! topLevel) {
                this.editingTab = item;
            } else {
                this.editingItem = item;
            }
        },

        itemUpdated(updatedConfig, item) {
            item.text = updatedConfig.display;

            this.updateItemManipulation(item, 'display', updatedConfig.display);
            this.updateItemManipulation(item, 'url', updatedConfig.url);
            this.updateItemAction(item);

            this.resetItemEditor();
            this.changed = true;
        },

        tabUpdated(tabDisplay, tabItem) {
            tabItem.text = tabDisplay;

            this.resetTabEditor();
            this.changed = true;
        },

        updateItemManipulation(item, key, value) {
            let currentAction = data_get(item.manipulations, 'action');

            if (currentAction === '@create' || value !== data_get(item.original, key)) {
                item.manipulations[key] = value;
            } else {
                Vue.delete(item.manipulations, key);
            }
        },

        updateItemAction(item) {
            if (this.isTabNode(item)) {
                return;
            }

            let detectedAction = this.detectItemAction(item);

            if (detectedAction) {
                item.manipulations.action = detectedAction;
            } else {
                Vue.delete(item.manipulations, 'action');
            }

            if (this.isChildItemNode(item)) {
                this.updateItemAction(item.parent);
            }
        },

        detectItemAction(item) {
            let currentAction = data_get(item.manipulations, 'action');

            switch (true) {
                case currentAction === '@create':
                    return '@create';
                case currentAction === '@alias':
                    return '@alias';
                case currentAction === '@hide':
                    return '@hide';
                case this.itemHasMoved(item):
                    return '@move';
                case this.itemHasBeenModified(item):
                    return '@modify';
            }

            return null;
        },

        itemHasMoved(item) {
            if (this.itemIsWithinOriginalParentItem(item)) {
                return false;
            }

            return this.itemHasMovedWithinTab(item)
                || this.itemHasMovedToAnotherTab(item);
        },

        itemIsWithinOriginalParentItem(item) {
            let parentsOriginalChildIds = data_get(item.parent, 'original', { children: [] })
                .children
                .map(child => child.id);

            return this.isChildItemNode(item) && parentsOriginalChildIds.includes(item.config.id);
        },

        itemHasMovedWithinTab(item) {
            let parentsOriginalChildIds = data_get(item.parent, 'original', { children: [] })
                .children
                .map(child => child.id);

            if (this.isChildItemNode(item) && ! parentsOriginalChildIds.includes(item.config.id)) {
                return true;
            }

            let currentTab = data_get(this.getParentTabNode(item), 'config.display_original', 'Top Level');
            let tabsOriginalIds = this.originalTabItems[currentTab];

            if (tabsOriginalIds === undefined) {
                return false;
            }

            if (! this.isChildItemNode(item) && ! tabsOriginalIds.includes(item.config.id)) {
                return true;
            }

            return false;
        },

        itemHasMovedToAnotherTab(item) {
            let currentTab = data_get(this.getParentTabNode(item), 'config.display_original', 'Top Level');
            let originalTab = data_get(item.original, 'tab') || data_get(item.parent, 'original.tab');

            return currentTab !== originalTab;
        },

        itemHasBeenModified(item) {
            return this.itemHasModifiedProperties(item)
                || this.itemHasModifiedChildren(item);
        },

        itemHasModifiedProperties(item) {
            return _.chain(item.manipulations).omit(['action', 'reorder', 'children']).keys().value().length > 0;
        },

        itemHasModifiedChildren(item) {
            return item.children.filter(childItem => {
                return _.chain(childItem.manipulations).keys().value().length > 0;
            }).length > 0;
        },

        expandAll() {
            this.traverseTree(this.topLevelTreeData, (node) => {
                if (! this.isTabNode(node)) {
                    this.$set(node, 'open', true);
                }
            });

            this.traverseTree(this.mainTreeData, (node) => {
                if (! this.isTabNode(node)) {
                    this.$set(node, 'open', true);
                }
            });
        },

        collapseAll() {
            this.traverseTree(this.topLevelTreeData, (node) => {
                if (! this.isTabNode(node)) {
                    this.$set(node, 'open', false);
                }
            });

            this.traverseTree(this.mainTreeData, (node) => {
                if (! this.isTabNode(node)) {
                    this.$set(node, 'open', false);
                }
            });
        },

        resetItemEditor() {
            this.editingItem = false;
            this.creatingItem = false;
            this.targetDataArray = false;
        },

        resetTabEditor() {
            this.editingTab = false;
            this.creatingTab = false;
        },

        pinItem(item) {
            this.aliasItem(item, this.topLevelTreeData);
        },

        aliasItem(item, treeData) {
            let currentAction = data_get(item.manipulations, 'action');
            let newItem = this.normalizeNavConfig(clone(item.config), false);

            if (currentAction === '@create') {
                newItem.manipulations = clone(item.manipulations);
            } else {
                newItem.manipulations = { action: '@alias' };
            }

            newItem.children = [];

            if (newItem.original) {
                newItem.original.children = [];
            }

            let tree = treeData || item.parent.children;

            tree.push(newItem);

            this.changed = true;
        },

        itemIsVisible(item) {
            return data_get(item.manipulations, 'action') !== '@hide';
        },

        isHideable(item) {
            let action = data_get(item.manipulations, 'action');

            if (this.isTabNode(item) && action === '@create') {
                return false;
            }

            return ! ['@alias', '@create'].includes(action);
        },

        removeItem(item, bypassConfirmation = false) {
            if (this.isCustomTabNode(item) && item.children.length && ! bypassConfirmation) {
                return this.confirmingRemoval = item;
            }

            item._vm.store.deleteNode(item);

            this.changed = true;
            this.confirmingRemoval = false;
        },

        hideItem(item) {
            Vue.set(item.manipulations, 'action', '@hide');

            this.updateItemAction(item);

            this.changed = true;
        },

        showItem(item) {
            Vue.delete(item.manipulations, 'action');

            this.updateItemAction(item);

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

            this.mainTreeData.forEach(tab => {
                tree.push({
                    'display': tab.text,
                    'display_original': tab.config.display_original || tab.text,
                    'action': tab.manipulations.action || false,
                    'items': this.prepareItemsForSubmission(tab.children),
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
            return data_get(item, 'original.id', item.text.toLowerCase().replaceAll(' ', '_'));
        },

    },

}
</script>
