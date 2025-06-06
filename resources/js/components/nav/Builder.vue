<template>
    <div>
        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1">{{ __(title) }}</h1>

                <dropdown-list class="ltr:mr-2 rtl:ml-2">
                    <dropdown-item
                        :text="__('Reset Nav Customizations')"
                        class="warning"
                        @click="confirmingReset = true"
                    ></dropdown-item>
                </dropdown-list>

                <a
                    @click="discardChanges"
                    class="text-2xs text-blue underline ltr:mr-4 rtl:ml-4"
                    v-if="isDirty"
                    v-text="__('Discard changes')"
                />

                <dropdown-list>
                    <template #trigger>
                        <button class="btn flex items-center ltr:pr-4 rtl:pl-4">
                            {{ __('Add Item') }}
                            <svg-icon name="micro/chevron-down-xs" class="w-2 ltr:ml-4 rtl:mr-4" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="addItem($refs.tree.rootChildren[0])" />
                    <dropdown-item :text="__('Add Section')" @click="addSection" />
                </dropdown-list>

                <div class="ltr:ml-4 ltr:text-left rtl:mr-4 rtl:text-right" :class="{ 'btn-group': hasSaveAsOptions }">
                    <button
                        class="btn-primary ltr:pl-4 rtl:pr-4"
                        :class="{ disabled: !changed }"
                        :disabled="!changed"
                        @click="save"
                        v-text="__('Save Changes')"
                    />

                    <dropdown-list v-if="hasSaveAsOptions" class="ltr:ml-0 rtl:mr-0">
                        <template #trigger>
                            <button class="btn-primary flex items-center ltr:rounded-l-none rtl:rounded-r-none">
                                <svg-icon name="micro/chevron-down-xs" class="w-2" />
                            </button>
                        </template>
                        <h6 class="p-2">{{ __('Save to') }}...</h6>
                        <dropdown-item
                            v-for="option in saveAsOptions"
                            :key="option.url"
                            @click="saveAs(option.url)"
                            class="group"
                        >
                            <div class="flex items-start ltr:pr-4 rtl:pl-4">
                                <svg-icon
                                    :name="option.icon"
                                    class="w-4 shrink-0 text-gray group-hover:text-white ltr:mr-2 rtl:ml-2"
                                />
                                <span class="whitespace-normal">{{ __(option.label) }}</span>
                            </div>
                        </dropdown-item>
                    </dropdown-list>
                </div>
            </div>
        </header>

        <div class="mb-2 flex justify-end">
            <a class="text-2xs text-blue underline ltr:mr-4 rtl:ml-4" v-text="__('Expand All')" @click="expandAll" />
            <a
                class="text-2xs text-blue underline ltr:mr-2 rtl:ml-2"
                v-text="__('Collapse All')"
                @click="collapseAll"
            />
        </div>

        <div v-if="!loading" class="page-tree w-full">
            <Draggable
                ref="tree"
                v-model="treeData"
                :node-key="(stat) => stat.data.id"
                :space="1"
                :indent="24"
                :dir="direction"
                :stat-handler="statHandler"
                keep-placeholder
                trigger-class="page-move"
                :drag-open="false"
                :each-draggable="eachDraggable"
                :each-droppable="eachDroppable"
                :root-droppable="rootDroppable"
                @change="changed = true"
                @before-drag-start="beforeDragStart"
                @after-drop="afterDrop"
            >
                <template #placeholder>
                    <div
                        class="w-full rounded-sm border border-dashed border-blue-400 bg-blue-500/10 p-2"
                        :class="{
                            'mt-8': isSectionNode(draggingStat),
                            'ml-[-24px]': isDraggingIntoTopLevel,
                        }"
                    >
                        &nbsp;
                    </div>
                </template>
                <template #default="{ node, stat }">
                    <top-level-tree-branch v-if="stat.level === 1 && stat.data?.text === 'Top Level'" :stat="stat" />
                    <tree-branch
                        v-else
                        :item="node"
                        :parent-section="getParentSectionNode(stat)"
                        :depth="stat.level"
                        :stat="stat"
                        :is-open="stat.open"
                        :is-child="isChildItemNode(stat)"
                        :has-children="stat.children.length > 0"
                        class="mb-px"
                        :class="{ 'mt-8': isSectionNode(stat) }"
                        @edit="editItem(stat)"
                        @toggle-open="stat.open = !stat.open"
                    >
                        <template #branch-options="{ isTopLevel }">
                            <dropdown-item v-if="stat.level < 3" :text="__('Add Item')" @click="addItem(stat)" />
                            <dropdown-item :text="__('Edit')" @click="editItem(stat)" />
                            <dropdown-item
                                v-if="!isSectionNode(stat) && !isTopLevel"
                                :text="__('Pin to Top Level')"
                                @click="pinItem(stat)"
                            />
                            <dropdown-item
                                v-if="!isSectionNode(stat)"
                                :text="__('Duplicate')"
                                @click="aliasItem(stat)"
                            />
                            <li class="divider" />
                            <dropdown-item
                                v-if="itemIsVisible(stat)"
                                :text="isHideable(stat) ? __('Hide') : __('Remove')"
                                class="warning"
                                @click="isHideable(stat) ? hideItem(stat) : removeItem(stat)"
                            />
                            <dropdown-item v-else :text="__('Show')" @click="showItem(stat)" />
                        </template>
                    </tree-branch>
                </template>
            </Draggable>
        </div>

        <item-editor
            v-if="creatingItem"
            :creating="true"
            :is-child="creatingItemIsChild"
            @closed="resetItemEditor"
            @updated="itemAdded"
        />

        <item-editor
            v-if="editingItem"
            :item="editingItem"
            :is-child="isChildItemNode(editingItem)"
            @closed="resetItemEditor"
            @updated="itemUpdated"
        />

        <section-editor v-if="creatingSection" :creating="true" @closed="resetSectionEditor" @updated="sectionAdded" />

        <section-editor
            v-if="editingSection"
            :section-item="editingSection"
            @closed="resetSectionEditor"
            @updated="sectionUpdated"
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
            :bodyText="__('Are you sure you want to remove this section and all of its children?')"
            :buttonText="__('Remove')"
            :danger="true"
            @confirm="removeItem(confirmingRemoval, true)"
            @cancel="confirmingReset = false"
        />
    </div>
</template>

<script>
import { dragContext, Draggable, walkTreeData } from '@he-tree/vue';
import TreeBranch from './Branch.vue';
import TopLevelTreeBranch from './TopLevelBranch.vue';
import ItemEditor from './ItemEditor.vue';
import SectionEditor from './SectionEditor.vue';
import { data_get } from '../../bootstrap/globals.js';

export default {
    components: {
        Draggable,
        TreeBranch,
        ItemEditor,
        SectionEditor,
        TopLevelTreeBranch,
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
            treeData: [],
            originalSectionItems: {},
            changed: false,
            targetStat: null,
            creatingItem: false,
            creatingItemIsChild: false,
            editingItem: false,
            creatingSection: false,
            editingSection: false,
            confirmingReset: false,
            confirmingRemoval: false,
            draggingStat: false,
            isDraggingIntoTopLevel: false,
        };
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
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

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    methods: {
        setInitialNav(nav) {
            let navConfig = clone(nav);

            this.setOriginalSectionItems(navConfig);

            this.treeData = Object.values(navConfig.map((section) => this.normalizeNavConfig(section)));
        },

        setOriginalSectionItems(nav) {
            nav.forEach(
                (section) => (this.originalSectionItems[section.display_original] = section.items_original || []),
            );
        },

        discardChanges() {
            this.setInitialNav(this.initialNav);

            this.changed = false;
        },

        normalizeNavConfig(config, isSectionNode = true) {
            let item = {
                text: config.display,
                config: config,
                original: config.original,
                manipulations: isSectionNode ? config : config.manipulations || {},
                isSection: isSectionNode,
                open: isSectionNode,
            };

            let children = config.items || config.children || [];

            if (children) {
                item.children = children.map((childItem) => {
                    return {
                        text: childItem.display,
                        children: childItem.children.map((childChildItem) =>
                            this.normalizeNavConfig(childChildItem, false),
                        ),
                        open: false,
                        config: childItem,
                        original: childItem.original,
                        manipulations: childItem.manipulations || {},
                        isSection: false,
                    };
                });
            }

            return item;
        },

        eachDraggable(stat) {
            // Prevent the top level item being dragged. It should always stay at the top.
            if (stat.data.text === 'Top Level') return false;

            return true;
        },

        // This method is called when an item is being dragged into a position that isn't the root level.
        // For root level behavior, see rootDroppable. (Why the package separated it into two methods is beyond me.)
        eachDroppable(targetStat) {
            // If the item being dragged is a section, we don't want it being dragged anywhere except the root level.
            if (this.isSectionNode(dragContext.dragNode)) return false;

            // We want to keep track of whether the item is being dragged into the top level.
            // This was the most appropriate place to hook into.
            this.isDraggingIntoTopLevel = this.checkIfDraggingIntoTopLevel();

            // We want to prevent creating a tree with too many levels.
            if (dragContext.dragNode.children.length && targetStat.level >= 2) return false;
            if (targetStat.level >= 3) return false;

            return true;
        },

        checkIfDraggingIntoTopLevel() {
            let stat = dragContext.closestNode;
            while (stat.level > 1) stat = stat.parent;
            return stat.data.text === 'Top Level';
        },

        // This method is called when an item is being dragged into a position at the root level.
        // For non-root level behavior, see eachDroppable. (Why the package separated it into two methods is beyond me.)
        rootDroppable() {
            // If there's no closest node, it means that we're dragging to the very top of the tree.
            // We don't want to allow dropping before the "top level" node.
            if (dragContext.closestNode === null) return false;

            // Only allow dropping sections at the root level.
            return this.isSectionNode(dragContext.dragNode);
        },

        isSectionNode(stat) {
            return stat?.data?.isSection;
        },

        isParentItemNode(stat) {
            return !this.isSectionNode(stat) && !this.isChildItemNode(stat);
        },

        isChildItemNode(stat) {
            if (!stat.parent) return false;

            return !this.isSectionNode(stat.parent);
        },

        isCustomSectionNode(stat) {
            return this.isSectionNode(stat) && stat.data?.manipulations?.action === '@create';
        },

        getParentSectionNode(stat) {
            if (!this.isSectionNode(stat) && stat !== undefined) {
                return this.getParentSectionNode(stat.parent);
            }

            return stat;
        },

        addItem(targetStat) {
            this.targetStat = targetStat;
            this.creatingItem = true;
            this.creatingItemIsChild = this.isParentItemNode(targetStat);
        },

        addSection() {
            this.creatingSection = true;
        },

        itemAdded(createdConfig) {
            let item = this.normalizeNavConfig(createdConfig, false);

            item.manipulations = {
                action: '@create',
                display: createdConfig.display,
                url: createdConfig.url,
                icon: createdConfig.icon,
            };

            this.$refs.tree.add(item, this.targetStat);
            this.resetItemEditor();
            this.changed = true;
        },

        sectionAdded(sectionDisplay) {
            let item = this.normalizeNavConfig({
                action: '@create',
                display: sectionDisplay,
                display_original: false,
            });

            this.$refs.tree.add(item);
            this.resetSectionEditor();
            this.changed = true;
        },

        editItem(stat) {
            if (this.isSectionNode(stat)) {
                this.editingSection = stat;
            } else {
                this.editingItem = stat;
            }
        },

        itemUpdated(updatedConfig, item) {
            item.data.text = updatedConfig.display;
            item.data.config.icon = updatedConfig.icon;

            this.updateItemManipulation(item, 'display', updatedConfig.display);
            this.updateItemManipulation(item, 'url', updatedConfig.url);
            this.updateItemManipulation(item, 'icon', updatedConfig.icon);
            this.updateItemAction(item);

            this.resetItemEditor();
            this.changed = true;
        },

        sectionUpdated(sectionDisplay, sectionItem) {
            sectionItem.data.text = sectionDisplay;

            this.resetSectionEditor();
            this.changed = true;
        },

        updateItemManipulation(stat, key, value) {
            let currentAction = stat.data.manipulations.action;

            if (currentAction === '@create' || value !== stat.data.original[key]) {
                stat.data.manipulations[key] = value;
            } else {
                delete stat.data.manipulations[key];
            }
        },

        updateItemAction(stat) {
            if (this.isSectionNode(stat)) {
                return;
            }

            let detectedAction = this.detectItemAction(stat);

            if (detectedAction) {
                stat.data.manipulations.action = detectedAction;
            } else {
                delete stat.data.manipulations.action;
            }

            if (this.isChildItemNode(stat)) {
                this.updateItemAction(stat.parent);
            }
        },

        detectItemAction(stat) {
            let currentAction = stat.data.manipulations.action;

            switch (true) {
                case currentAction === '@create':
                    return '@create';
                case currentAction === '@alias':
                    return '@alias';
                case currentAction === '@hide':
                    return '@hide';
                case this.itemHasMoved(stat):
                    return '@move';
                case this.itemHasBeenModified(stat):
                    return '@modify';
            }

            return null;
        },

        itemHasMoved(stat) {
            if (this.itemIsWithinOriginalParentItem(stat)) {
                return false;
            }

            return this.itemHasMovedWithinSection(stat) || this.itemHasMovedToAnotherSection(stat);
        },

        itemIsWithinOriginalParentItem(stat) {
            let parentOriginal = stat.parent.data.original || { children: [] };
            let parentsOriginalChildIds = parentOriginal.children.map((child) => child.id);

            return this.isChildItemNode(stat) && parentsOriginalChildIds.includes(stat.data.config.id);
        },

        itemHasMovedWithinSection(stat) {
            let parentOriginal = stat.parent.data.original || { children: [] };

            let parentsOriginalChildIds = parentOriginal.children.map((child) => child.id);

            if (this.isChildItemNode(stat) && !parentsOriginalChildIds.includes(stat.data.config.id)) {
                return true;
            }

            let currentSection = this.getParentSectionNode(stat).data?.config?.display_original || 'Top Level';
            let sectionsOriginalIds = this.originalSectionItems[currentSection];

            if (sectionsOriginalIds === undefined) {
                return false;
            }

            if (!this.isChildItemNode(stat) && !sectionsOriginalIds.includes(stat.data.config.id)) {
                return true;
            }

            return false;
        },

        itemHasMovedToAnotherSection(stat) {
            let currentSection = this.getParentSectionNode(stat).data.config?.display_original || 'Top Level';
            let originalSection = stat.data.original.section || stat.parent.data.original.section;

            return currentSection !== originalSection;
        },

        itemHasBeenModified(stat) {
            return this.itemHasModifiedProperties(stat) || this.itemHasModifiedChildren(stat);
        },

        itemHasModifiedProperties(stat) {
            const { action, reorder, children, ...remaining } = stat.data.manipulations;

            return Object.keys(remaining).length > 0;
        },

        itemHasModifiedChildren(stat) {
            return (
                stat.children.filter((childItem) => {
                    return Object.keys(childItem.data.manipulations).length > 0;
                }).length > 0
            );
        },

        expandAll() {
            walkTreeData(this.$refs.tree.rootChildren, (stat) => {
                if (!this.isSectionNode(stat)) {
                    stat.open = true;
                }
            });
        },

        collapseAll() {
            walkTreeData(this.$refs.tree.rootChildren, (stat) => {
                if (!this.isSectionNode(stat)) {
                    stat.open = false;
                }
            });
        },

        resetItemEditor() {
            this.editingItem = false;
            this.creatingItem = false;
            this.creatingItemIsChild = false;
            this.targetStat = false;
        },

        resetSectionEditor() {
            this.editingSection = false;
            this.creatingSection = false;
        },

        pinItem(stat) {
            this.aliasItem(stat, this.$refs.tree.rootChildren[0]);
        },

        aliasItem(stat, parentStat) {
            let currentAction = stat.data.manipulations.action;
            let newItem = this.normalizeNavConfig({ ...stat.data.config }, false);

            if (currentAction === '@create') {
                newItem.manipulations = { ...stat.data.manipulations };
            } else {
                newItem.manipulations = { action: '@alias' };
            }

            newItem.children = [];

            if (newItem.original) {
                newItem.original.children = [];
            }

            parentStat = parentStat || stat.parent;

            this.$refs.tree.add(newItem, parentStat);

            this.changed = true;
        },

        itemIsVisible(stat) {
            return stat.data?.manipulations?.action !== '@hide';
        },

        isHideable(stat) {
            let action = stat.data.manipulations.action;

            if (this.isSectionNode(stat) && action === '@create') {
                return false;
            }

            return !['@alias', '@create'].includes(action);
        },

        removeItem(stat, bypassConfirmation = false) {
            if (this.isCustomSectionNode(stat) && stat.children.length && !bypassConfirmation) {
                return (this.confirmingRemoval = stat);
            }

            this.$refs.tree.remove(stat);

            this.changed = true;
            this.confirmingRemoval = false;
        },

        hideItem(stat) {
            stat.data.manipulations.action = '@hide';

            this.updateItemAction(stat);

            this.changed = true;
        },

        showItem(stat) {
            delete stat.data.manipulations['action'];

            this.updateItemAction(stat);

            this.changed = true;
        },

        reset() {
            this.$axios
                .delete(this.destroyUrl)
                .then(() => window.location.reload())
                .catch(() => this.$toast.error(__('Something went wrong')));
        },

        save() {
            if (!this.changed) {
                return;
            }

            this.saveAs(this.updateUrl);
        },

        saveAs(url) {
            let tree = this.preparePreferencesSubmission();

            this.$axios
                .patch(url, { tree })
                .then(() => location.reload())
                .catch(() => this.$toast.error(__('Something went wrong')));
        },

        preparePreferencesSubmission() {
            let tree = [];

            this.treeData.forEach((section) => {
                tree.push({
                    display: section.text,
                    display_original: section.config.display_original || section.text,
                    action: section.manipulations.action || false,
                    items: this.prepareItemsForSubmission(section.children),
                });
            });

            return tree;
        },

        prepareItemsForSubmission(treeItems) {
            let items = [];

            treeItems.forEach((item) => {
                items.push({
                    id: this.prepareItemIdForSubmission(item),
                    manipulations: item.manipulations,
                    children: item.children ? this.prepareItemsForSubmission(item.children) : [],
                });
            });

            return items;
        },

        prepareItemIdForSubmission(item) {
            return data_get(item, 'original.id', item.text.toLowerCase().replaceAll(' ', '_'));
        },

        statHandler(stat) {
            stat.open = stat.data.open;
            return stat;
        },

        beforeDragStart(stat) {
            this.draggingStat = stat;
        },

        afterDrop() {
            this.updateItemAction(this.draggingStat);
            this.draggingStat = false;
            return true;
        },
    },
};
</script>
