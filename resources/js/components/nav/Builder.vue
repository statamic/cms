<template>

    <div>

        <header class="mb-3">
            <!--
            <breadcrumb url="" :title="__('Preferences')" />
            -->

            <div class="flex items-center">
                <h1 class="flex-1">{{ __('Nav Preferences') }}</h1>

                <a @click="$refs.mainTree.cancel" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list>
                    <template #trigger>
                        <button
                            class="btn"
                            :class="{ 'flex items-center pr-2': true }"
                            @click=""
                        >
                            {{ __('Add Item') }}
                            <svg-icon name="chevron-down-xs" class="w-2 ml-2" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="addItem(topLevelTreeData)" />
                    <dropdown-item :text="__('Add Section')" @click="addSection" />
                </dropdown-list>

                <button
                    class="btn-primary ml-2"
                    :class="{ 'disabled': !changed }"
                    :disabledd="!changed"
                    @click="save"
                    v-text="__('Save Changes')" />
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
                @drag="topLevelTreeDragStart"
                @nodeOpenChanged=""
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    :disable-sections="true"
                    @edit="$emit('edit-page', item, vm, store, $event)"
                    @toggle-open="store.toggleOpen(item)"
                    @removed=""
                    @children-orphaned=""
                >
                    <template #branch-options="{ item, removeBranch, orphanChildren, vm, depth }">
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
                @change=""
                @drag="mainTreeDragStart"
                @nodeOpenChanged=""
            >
                <tree-branch
                    slot-scope="{ data: item, store, vm }"
                    :item="item"
                    :depth="vm.level"
                    :vm="vm"
                    :is-open="item.open"
                    :has-children="item.children.length > 0"
                    @edit="$emit('edit-page', item, vm, store, $event)"
                    @toggle-open="store.toggleOpen(item)"
                    @removed=""
                    @children-orphaned=""
                >
                    <template #branch-options="{ item, removeBranch, orphanChildren, vm, depth }">
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
        initialNav: {
            required: true,
        },
    },

    data() {
        return {
            loading: false,
            topLevelTreeData: [],
            mainTreeData: [],
            changed: false,
            targetDataArray: null,
            creatingItem: false,
            editingItem: false,
            creatingSection: false,
            editingSection: false,
        }
    },

    mounted() {
        this.topLevelTreeData = _.chain(this.initialNav['Top Level'].items)
            .map((section) => this.normalizeNavConfig(section))
            .values()
            .value();

        this.mainTreeData = _.chain(this.initialNav)
            .reject((items, section) => section === 'Top Level')
            .mapObject((section) => this.normalizeNavConfig(section))
            .values()
            .value();
    },

    computed: {

        isDirty() {
            return this.$dirty.has('nav-preferences');
        },

    },

    methods: {

        normalizeNavConfig(config) {
            let item = {
                text: config.display,
                original: data_get(config, 'original', config.display),
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
            let item = {
                action: '@create',
                ...this.normalizeNavConfig(createdConfig),
            };

            this.targetDataArray.push(item);
            this.resetItemEditor();
            this.changed = true;
        },

        sectionAdded(sectionDisplay) {
            let item = this.normalizeNavConfig({
                display: sectionDisplay,
                original: false,
            });

            this.mainTreeData.push(item);
            this.resetSectionEditor();
            this.changed = true;
        },

        editItem(item) {
            if (this.isSectionNode(item)) {
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
                url: updatedConfig.url,
            };

            this.resetItemEditor();
            this.changed = true;
        },

        sectionUpdated(sectionDisplay, sectionItem) {
            sectionItem.text = sectionDisplay;

            this.resetSectionEditor();
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
        },

        itemIsVisible(item) {
            return item.manipulations.action !== '@remove';
        },

        isHideable(item) {
            let action = data_get(item.manipulations, 'action');

            return action !== '@alias' && action !== '@move';
        },

        removeItem(item) {
            item._vm.store.deleteNode(item);
        },

        hideItem(item) {
            item.trashedManipulations = item.manipulations;

            item.manipulations = { action: '@remove' };
        },

        showItem(item) {
            Vue.delete(item.manipulations, 'action');

            if (item.trashedManipulations) {
                item.manipulations = item.trashedManipulations;
            }
        },

        save() {
            let tree = this.preparePreferencesSubmission();

            this.$axios
                .post('/cp/nav-preferences', {tree})
                .then(response => this.$toast.success(__('Saved')))
                .catch(error => this.$toast.error(__('Something went wrong')));
        },

        preparePreferencesSubmission() {
            let tree = [];

            tree.push({
                'section': 'Top Level',
                'manipulations': this.prepareItemsForSubmission(this.topLevelTreeData),
            });

            this.mainTreeData.forEach(section => {
                tree.push({
                    'section': section.text,
                    'original': section.original,
                    'manipulations': this.prepareItemsForSubmission(section.children),
                });
            });

            return tree;
        },

        prepareItemsForSubmission(treeItems) {
            let items = [];

            treeItems.forEach(item => {
                items.push({
                    'id': item.config.id.replace('::clone', ''),
                    'manipulations': item.manipulations,
                    'children': item.children ? this.prepareItemsForSubmission(item.children) : [],
                });
            });

            return items;
        },

    },

}
</script>
