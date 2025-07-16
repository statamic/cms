<template>
    <div>
        <Header :title="title" icon="preferences">
            <Dropdown placement="left-start">
                <DropdownMenu>
                    <DropdownItem :text="__('Reset Nav Customizations')" variant="destructive" icon="history" @click="confirmingReset = true" />
                </DropdownMenu>
            </Dropdown>

            <Button
                v-if="isDirty"
                variant="filled"
                :text="__('Discard changes')"
                @click="$refs.tree.cancel"
            />

            <Dropdown placement="left-start">
                <template #trigger>
                    <Button :text="__('Add')" icon-append="ui/chevron-down" />
                </template>
                <DropdownMenu>
                    <DropdownItem :text="__('Add Nav Item')" @click="addItem($refs.tree.rootChildren[0])" icon="add-list" />
                    <DropdownItem :text="__('Add Section')" @click="addSection" icon="add" />
                </DropdownMenu>
            </Dropdown>

            <ButtonGroup>
                <Button type="submit" variant="primary" :disabled="!changed" :text="__('Save')" @click="save" />

                <Dropdown align="end" v-if="hasSaveAsOptions">
                    <template #trigger>
                        <Button icon="ui/chevron-down" variant="primary" />
                    </template>
                    <DropdownMenu>
                        <DropdownLabel>{{ __('Save to') }}...</DropdownLabel>
                        <DropdownItem
                            v-for="option in saveAsOptions"
                            :key="option.url"
                            :text="option.label"
                            @click="saveAs(option.url)"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ButtonGroup>
        </Header>

        <Panel class="nav-builder">
            <div class="loading card" v-if="loading">
                <loading-graphic />
            </div>

            <PanelHeader>
                <div class="page-tree-header font-medium text-sm items-center flex justify-between">
                    <div v-text="__('Navigation')" />
                    <div class="flex gap-2 -me-3">
                        <ui-button size="sm" icon="tree-collapse" :text="__('Collapse')" @click="collapseAll" />
                        <ui-button size="sm" icon="tree-expand" :text="__('Expand')" @click="expandAll" />
                    </div>
                </div>
            </PanelHeader>
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
                    :each-draggable="eachDraggable"
                    :each-droppable="eachDroppable"
                    :root-droppable="rootDroppable"
                    @change="changed = true"
                    @before-drag-start="beforeDragStart"
                    @after-drop="afterDrop"
                >
                    <template #placeholder>
                        <div
                            class="w-full rounded-lg border border-dashed border-blue-400 bg-blue-500/10 p-2"
                            :class="{
                            'mt-6': isSectionNode(draggingStat),
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
                            :class="{ 'mt-6': isSectionNode(stat) }"
                            @edit="editItem(stat)"
                            @toggle-open="stat.open = !stat.open"
                        >
                            <template #branch-options="{ isTopLevel }">
                                <DropdownItem v-if="stat.level < 3" :text="__('Add Item')" @click="addItem(stat)" />
                                <DropdownItem :text="__('Edit')" @click="editItem(stat)" />
                                <DropdownItem
                                    v-if="!isSectionNode(stat) && !isTopLevel"
                                    :text="__('Pin to Top Level')"
                                    @click="pinItem(stat)"
                                />
                                <DropdownItem
                                    v-if="!isSectionNode(stat)"
                                    :text="__('Duplicate')"
                                    @click="aliasItem(stat)"
                                />
                                <DropdownSeparator />
                                <DropdownItem
                                    v-if="itemIsVisible(stat)"
                                    :text="isHideable(stat) ? __('Hide') : __('Remove')"
                                    variant="destructive"
                                    @click="isHideable(stat) ? hideItem(stat) : removeItem(stat)"
                                />
                                <DropdownItem v-else :text="__('Show')" @click="showItem(stat)" />
                            </template>
                        </tree-branch>
                    </template>
                </Draggable>
            </div>
        </Panel>

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
import { Header, Button, ButtonGroup, Dropdown, DropdownMenu, DropdownItem, DropdownSeparator, DropdownLabel, Panel, PanelHeader } from '@statamic/ui';

export default {
    components: {
        Header,
        DropdownLabel,
        Button,
        ButtonGroup,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        Draggable,
        TreeBranch,
        ItemEditor,
        SectionEditor,
        TopLevelTreeBranch,
        Panel,
        PanelHeader,
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
