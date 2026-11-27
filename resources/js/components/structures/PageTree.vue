<template>
    <div>
        <div v-if="!loading && treeData.length == 0" class="no-results flex w-full items-center">
            <slot name="empty" />
        </div>

        <ui-panel v-show="treeData.length">
            <div class="loading card" v-if="loading">
                <Icon name="loading" />
            </div>

            <ui-panel-header>
                <div class="page-tree-header font-medium text-sm items-center flex justify-between">
                    <div v-text="__('Tree Structure')" />
                    <div class="flex gap-2">
                        <ui-button size="sm" icon="tree-collapse" :text="__('Collapse')" @click="collapseAll" />
                        <ui-button size="sm" icon="tree-expand" :text="__('Expand')" @click="expandAll" />
                    </div>
                </div>
            </ui-panel-header>
            <div v-if="!loading" class="page-tree" :class="{ 'page-tree--ready': ready }" @focusout="queueDropTreeTabStop">
                <Draggable
                    ref="tree"
                    v-model="treeData"
                    :disable-drag="!editable"
                    :space="1"
                    :indent="24"
                    :dir="direction"
                    :node-key="(stat) => stat.data.id"
                    :dragOverThrottleInterval="30"
                    :each-droppable="eachDroppable"
                    :max-level="maxDepth"
                    :stat-handler="statHandler"
                    :i18n="treeDraggableI18n"
                    :aria-label="__('Tree Structure')"
                    @after-drop="afterDrop"
                    @open:node="nodeOpened"
                    @close:node="nodeClosed"
                >
                    <template #placeholder>
                        <div class="w-full rounded-sm border border-dashed border-blue-400 bg-blue-500/10 p-2">&nbsp;</div>
                    </template>

                    <template #default="{ node, stat }">
                        <tree-branch
                            :ref="`branch-${node.id}`"
                            :page="node"
                            :stat="stat"
                            :depth="stat.level"
                            :first-page-is-root="expectsRoot"
                            :is-open="stat.open"
                            :has-children="stat.children.length > 0"
                            :show-slugs="showSlugs"
                            :show-blueprint="blueprints?.length > 1"
                            :editable="editable"
                            :root="isRoot(stat)"
                            @edit="$emit('edit-page', node, $event)"
                            @toggle-open="stat.open = !stat.open"
                            @removed="pageRemoved"
                            @branch-clicked="$emit('branch-clicked', node)"
                            class="mb-px"
                        >
                            <template #branch-action="props">
                                <slot name="branch-action" v-bind="{ ...props, stat }" />
                            </template>

                            <template #branch-icon="props">
                                <slot name="branch-icon" v-bind="{ ...props, stat }" />
                            </template>

                            <template #branch-options="props">
                                <slot name="branch-options" v-bind="{ ...props, stat }" />
                            </template>

                            <template #branch-options-dropdown="props">
                                <slot name="branch-options-dropdown" v-bind="{ ...props, stat }" />
                            </template>
                        </tree-branch>
                    </template>
                </Draggable>
            </div>
        </ui-panel>

        <confirmation-modal
            :open="discardingChanges"
            :title="__('Discard Changes')"
            :body-text="__('Are you sure?')"
            :button-text="__('Discard Changes')"
            :danger="true"
            @confirm="confirmDiscard"
            @cancel="discardingChanges = false"
        />
    </div>
</template>

<script>
import { dragContext, Draggable, walkTreeData } from '@he-tree/vue';
import TreeBranch from './Branch.vue';
import { PanelHeader, Panel, Icon } from '@/components/ui';
import { clone } from '@/bootstrap/globals.js';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        Draggable,
        TreeBranch,
        PanelHeader,
        Panel,
        Icon,
    },

    props: {
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String },
        submitParameters: { type: Object, default: () => ({}) },
        createUrl: { type: String },
        site: { type: String, required: true },
        localizations: { type: Array },
        maxDepth: { type: Number, default: Infinity },
        expectsRoot: { type: Boolean, required: true },
        showSlugs: { type: Boolean, default: false },
        preferencesPrefix: { type: String },
        editable: { type: Boolean, default: true },
        blueprints: { type: Array },
    },

    // Moving a page with the keyboard (see Branch.vue): each branch asks the tree what it can do and has it do the moving, so the same rules
    // apply as for dragging. Only an editable tree offers it.
    provide() {
        if (!this.editable) return {};

        return {
            pageTreeKeyboard: {
                siblingsOf: (stat) => this.siblingsOf(stat),
                indexOf: (stat) => this.siblingsOf(stat).indexOf(stat),
                canMoveUp: (stat) => this.canKeyboardMove(stat, 'up'),
                canMoveDown: (stat) => this.canKeyboardMove(stat, 'down'),
                canIndent: (stat) => this.canKeyboardMove(stat, 'in'),
                canOutdent: (stat) => this.canKeyboardMove(stat, 'out'),
                move: (stat, direction) => this.keyboardMove(stat, direction),
                positionKey: (stat) => `${stat.parent?.data.id ?? ''}|${this.siblingsOf(stat).indexOf(stat)}|${stat.level}`,
            },
        };
    },

    data() {
        return {
            loading: false,
            saving: false,
            pages: [],
            treeData: [],
            collapsedState: [],
            discardingChanges: false,
            ready: false,
            saveKeyBinding: null,
        };
    },

    computed: {
        activeLocalization() {
            return this.localizations.find((l) => l.active);
        },

        preferencesKey() {
            return this.preferencesPrefix ? `${this.preferencesPrefix}.${this.site}.pagetree` : null;
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },

        treeDraggableI18n() {
            return {
                instructions: __('messages.tree_aria_instructions'),
            };
        },
    },

    watch: {
        site(site) {
            this.getPages();
        },

        collapsedState: {
            deep: true,
            handler(state) {
                if (this.preferencesKey) {
                    localStorage.setItem(this.preferencesKey, JSON.stringify(state));
                }
            },
        },
    },

    created() {
        this.collapsedState = this.getCollapsedState();

        this.getPages().then(() => {
            this.initialPages = clone(this.pages);
        });

        // A read-only tree can't be saved, so binding the shortcut would only take it away
        // from whatever is behind it. e.g. an entry being edited under a selector stack.
        if (this.editable) {
            this.saveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
                e.preventDefault();
                this.save();
            });
        }
    },

    mounted() {
        setTimeout(() => this.ready = true, 500); // arbitrary delay after initial transitions
    },

    beforeUnmount() {
        this.unwatchTreeTabStop?.();
        this.saveKeyBinding?.destroy();
    },

    methods: {
        isRoot(stat) {
            if (!this.expectsRoot) {
                return false;
            }

            return stat.level === 1 && stat.data.id === this.treeData[0]?.id;
        },

        getPages() {
            this.loading = true;
            const url = `${this.pagesUrl}?site=${this.site}`;

            return this.$axios.get(url).then((response) => {
                this.pages = response.data.pages;
                this.updateTreeData();
                this.loading = false;
                this.$nextTick(() => this.watchTreeTabStop());
                this.$emit('loaded', this.pages);
            });
        },

        // The tree makes one page a Tab stop whenever its pages change (a move, opening or closing one), so Tab sometimes landed on a
        // page and sometimes went straight to its drag handle. Tab goes from handle to handle instead, and a page only takes focus
        // when the arrow keys move onto it.
        watchTreeTabStop() {
            this.unwatchTreeTabStop?.();
            this.unwatchTreeTabStop = this.$watch(() => this.$refs.tree?.activeDescendant, () => this.queueDropTreeTabStop());
        },

        // After the tree has moved focus (it does that on the next tick), so it's the page focus ended up on that counts
        queueDropTreeTabStop() {
            setTimeout(() => this.dropTreeTabStop());
        },

        dropTreeTabStop() {
            const tree = this.$refs.tree;
            const focused = document.activeElement;

            if (!tree?.activeDescendant || (focused?.matches('.tree-node') && tree.$el.contains(focused))) return;

            tree.activeDescendant = null;
        },

        refresh() {
            return this.getPages().then(() => {
                this.initialPages = clone(this.pages);
            });
        },

        treeUpdated() {
            this.pages = this.$refs.tree.getData();
            this.$emit('changed', this.pages);
        },

        afterDrop() {
            const root = this.$refs.tree.getData()[0];

            // Prevent items with children being moved to the root position
            if (this.expectsRoot && root.id !== this.pages[0].id && root.children?.length > 0) {
                const { dragNode, parent, indexBeforeDrop } = dragContext.startInfo;
                this.$refs.tree.move(dragNode, parent, indexBeforeDrop);
                return;
            }

            this.treeUpdated();
        },

        cleanPagesForSubmission(pages) {
            return pages.map((page) => ({
                id: page.id,
                children: this.cleanPagesForSubmission(page.children),
            }));
        },

        save() {
            if (!this.editable) {
                return;
            }

            this.saving = true;

            const payload = {
                pages: this.cleanPagesForSubmission(this.pages),
                site: this.site,
                expectsRoot: this.expectsRoot,
                ...this.submitParameters,
            };

            return this.$axios
                .patch(this.submitUrl, payload)
                .then((response) => {
                    if (!response.data.saved) {
                        return this.$toast.error(`Couldn't save tree`);
                    }

                    this.$emit('saved', response);
                    this.$toast.success(__('Saved'));
                    this.initialPages = this.pages;
                    return response;
                })
                .catch((e) => {
                    let message = e.response ? e.response.data.message : __('Something went wrong');

                    // For a validation error, show the first message from any field in the toast.
                    if (e.response && e.response.status === 422) {
                        const { errors } = e.response.data;
                        message = errors[Object.keys(errors)[0]][0];
                    }

                    this.$toast.error(message);
                    return Promise.reject(e);
                })
                .finally(() => (this.saving = false));
        },

        addPages(pages, targetParent) {
            this.$refs.tree.addMulti(pages, targetParent);

            this.treeUpdated();
        },

        updateTreeData() {
            this.treeData = [...this.pages];
        },

        pageRemoved(stat, deleteChildren) {
            // If we aren't deleting children, then they need to be moved into
            // the parent. The tree.move() API wasn't working, so instead
            // we're removing the child data and re-adding them as new ones.
            const children = [];
            if (!deleteChildren) stat.children.forEach((child) => children.push({ ...child.data }));

            this.$refs.tree.batchUpdate(() => {
                this.$refs.tree.remove(stat);
                if (children.length) this.$refs.tree.addMulti(children, stat.parent);
            });

            this.treeUpdated();
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
            router.get(localization.url);
        },

        createLocalization(localization) {
            console.log('todo.');
        },

        cancel() {
            this.discardingChanges = true;
        },

        confirmDiscard() {
            this.pages = this.initialPages;
            this.updateTreeData();
            this.$emit('canceled');
            this.discardingChanges = false;
        },

        eachDroppable(targetStat) {
            if (!this.expectsRoot) {
                return true;
            }

            return !this.isRoot(targetStat);
        },

        pageUpdated() {
            this.pages = this.$refs.tree.getData();
            this.$emit('changed', this.pages);
        },

        depthOf(page) {
            return this.$refs.tree.getStat(page).level;
        },

        expandAll() {
            this.$refs.tree.openAll();
            this.collapsedState = [];
        },

        collapseAll() {
            this.$refs.tree.closeAll();
            // Get all node IDs to mark them as collapsed
            this.collapsedState = [];
            walkTreeData(this.treeData, (node) => {
                this.collapsedState.push(node.id);
            });
        },

        getNodeByBranchId(id) {
            let branch;
            walkTreeData(this.treeData, (node) => {
                if (node.id === id) {
                    branch = node;
                    return false;
                }
            });
            return branch;
        },

        getCollapsedState() {
            if (!this.preferencesKey) return [];

            return JSON.parse(localStorage.getItem(this.preferencesKey) || '[]');
        },

        nodeOpened(node) {
            const index = this.collapsedState.indexOf(node.data.id);
            if (index > -1) this.collapsedState.splice(index, 1);
        },

        nodeClosed(node) {
            this.collapsedState.push(node.data.id);
        },

        // The pages beside this one, at its level: its parent's children, or the top level
        siblingsOf(stat) {
            return stat.parent ? stat.parent.children : (this.$refs.tree?.stats ?? []);
        },

        // How many levels this page and its children take up, counting itself as one
        heightOf(stat) {
            return 1 + Math.max(0, ...stat.children.map((child) => this.heightOf(child)));
        },

        // Whether a page can move up or down past the page beside it, in under the page above it, or out a level. The same rules as
        // dragging: nothing goes above, or into, the root page, and nothing nests deeper than the structure's max depth.
        canKeyboardMove(stat, direction) {
            if (this.isRoot(stat)) return false;

            const siblings = this.siblingsOf(stat);
            const index = siblings.indexOf(stat);
            const firstMovable = this.expectsRoot && !stat.parent ? 1 : 0;

            switch (direction) {
                case 'up':
                    return index > firstMovable;
                case 'down':
                    return index > -1 && index < siblings.length - 1;
                case 'in': {
                    const above = siblings[index - 1];
                    if (!above || this.isRoot(above)) return false;
                    return stat.level + this.heightOf(stat) <= this.maxDepth;
                }
                case 'out':
                    return !!stat.parent;
            }

            return false;
        },

        keyboardMove(stat, direction) {
            if (!this.canKeyboardMove(stat, direction)) return;

            const tree = this.$refs.tree;
            const siblings = this.siblingsOf(stat);
            const index = siblings.indexOf(stat);

            if (direction === 'up' || direction === 'down') {
                tree.move(stat, stat.parent || null, direction === 'up' ? index - 1 : index + 1);
            } else if (direction === 'in') {
                // Into the page above, as its last child, opening it so you can see where it went. Opening it fires the tree's open
                // event, and nodeOpened takes it out of the collapsed state.
                const above = siblings[index - 1];
                above.open = true;
                tree.move(stat, above, above.children.length);
            } else if (direction === 'out') {
                // Out of its parent, to just after it
                const parent = stat.parent;
                tree.move(stat, parent.parent || null, this.siblingsOf(parent).indexOf(parent) + 1);
            }

            this.treeUpdated();
        },

        statHandler(stat) {
            stat.open = !this.collapsedState.includes(stat.data.id);
            return stat;
        },
    },
};
</script>
