<template>
    <div
        class="page-tree-branch flex"
        :class="{ 'page-tree-branch--has-children': hasChildren, 'focus-outline': moving }"
        :data-moving="moving || undefined"
    >
        <slot name="branch-action" :branch="page">
            <!-- The handle: drag it, or click it or press Space to move it with the arrow keys. Focus comes back here when you're done. -->
            <DragHandle
                v-if="editable && canKeyboardMove"
                keyboard-reorder
                :keyboard-instructions="__('messages.keyboard_tree_reorder_handle_instructions')"
                class="w-6 shrink-0 self-stretch h-auto!"
                data-reorder-focus
                @keyboard-reorder="startMoving('start')"
            />
            <div v-else-if="editable" class="page-move w-6" />
        </slot>
        <!-- Holds focus while it's moving, so the arrow keys come to it rather than the tree. Only there while moving, as the tree's
             items take their names from everything in them, and its instructions would be read out on every one. -->
        <button
            v-if="canKeyboardMove && moving"
            ref="reorderFocusEl"
            type="button"
            class="sr-only"
            :aria-label="__('messages.keyboard_tree_reorder_instructions')"
        />
        <div v-if="canKeyboardMove" class="sr-only" aria-live="polite">{{ reorderStatus }}</div>
        <!-- While it's being moved with the keyboard: ↑ ↓ outside the row, on the side it was started from (the handle or the menu), as
             replicator sets do. ← → move it in and out a level too, but the hint keeps to the two, as four keys was too busy. -->
        <KeyboardReorderIndicator :moving="moving" :side="moveOrigin" />
        <div class="flex flex-1 items-center px-1.5 text-xs leading-normal">
            <div class="flex gap-2 sm:gap-3 grow items-center py-3" @click="$emit('branch-clicked', page)">
                <ui-status-indicator :status="page.status" v-tooltip="getStatusTooltip()" />
                <ui-icon v-if="isRoot" name="home" class="size-4" v-tooltip="__('This is the root page')" />
                <a
                    @click.prevent="$emit('edit', $event)"
                    :class="{ 'text-sm font-medium is-top-level-branch': isTopLevelBranch }"
                    :href="page.edit_url"
                    v-text="title"
                />

                <span
                    v-if="showSlugs"
                    class="pt-[2px] font-mono text-2xs text-gray-700 dark:text-gray-500"
                >
                    {{ slugPath }}
                </span>

                <ui-button
                    v-if="hasChildren"
                    class="transition duration-100 [&_svg]:size-4! -mx-1.5 -my-1"
                    icon="chevron-down"
                    size="xs"
                    round
                    variant="ghost"
                    :class="{ '-rotate-90 is-closed': !isOpen, 'is-open': isOpen }"
                    :aria-label="isOpen ? __('Collapse') : __('Expand')"
                    :aria-expanded="isOpen"
                    @click.stop="$emit('toggle-open')"
                />

                <div v-if="page.collection && editable" class="flex items-center gap-2">
                    <Icon name="navigation" class="size-3.5 text-gray-500" />
                    <div>
                        <Link :href="page.collection.create_url" v-text="__('Add')" class="hover:text-ui-accent-text" />
                        <span class="mx-1 text-gray-400 dark:text-gray-500">/</span>
                        <Link :href="page.collection.edit_url" v-text="__('List')" class="hover:text-ui-accent-text" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <ui-badge
                    v-if="showBlueprint && page.entry_blueprint"
                    :text="__(page.entry_blueprint.title)"
                    size="sm"
                    variant="filled"
                    v-tooltip="__('Blueprint')"
                />

                <slot name="branch-icon" :branch="page" />

                <template v-if="editable">
                    <slot
                        name="branch-options-dropdown"
                        :branch="page"
                        :depth="depth"
                        :remove-branch="remove"
                        :is-root="isRoot"
                        :can-move="canKeyboardMove"
                        :start-moving="() => startMoving('end')"
                    >
                        <Dropdown placement="left-start" :class="{ invisible: isRoot }">
                            <DropdownMenu>
                                <DropdownItem v-if="canKeyboardMove" :text="__('Move')" icon="move-vertical" @click="startMoving('end')" />
                                <DropdownSeparator v-if="canKeyboardMove" />
                                <slot
                                    name="branch-options"
                                    :branch="page"
                                    :depth="depth"
                                    :remove-branch="remove"
                                />
                            </DropdownMenu>
                        </Dropdown>
                    </slot>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, inject } from 'vue';
import { Dropdown, DropdownItem, DropdownMenu, DropdownSeparator, DragHandle, Icon } from '@/components/ui';
import { Link } from '@inertiajs/vue3';
import { useKeyboardItemReorder } from '@/composables/keyboard-item-reorder.js';
import KeyboardReorderIndicator from '@/components/sortable/KeyboardReorderIndicator.vue';

export default {
    components: { Link, Dropdown, DropdownItem, DropdownMenu, DropdownSeparator, DragHandle, Icon, KeyboardReorderIndicator },
    props: {
        page: Object,
        depth: Number,
        root: Boolean,
        firstPageIsRoot: Boolean,
        isOpen: Boolean,
        hasChildren: Boolean,
        showBlueprint: Boolean,
        showSlugs: Boolean,
        editable: { type: Boolean, default: true },
        stat: Object,
    },

    // Moving it with the keyboard, as replicator sets do (see keyboard-item-reorder.js): ↑ and ↓ within its level, → to nest it under the
    // page above, ← to move it out a level. The tree (PageTree.vue) knows the rules (max depth, the root page) and does the moving.
    setup(props) {
        const tree = inject('pageTreeKeyboard', null);
        const canKeyboardMove = computed(() => !!tree && props.editable && !props.root);

        const {
            moving,
            moveOrigin,
            rootEl: reorderFocusEl,
            status: reorderStatus,
            startMoving,
        } = useKeyboardItemReorder({
            index: () => tree?.indexOf(props.stat) ?? 0,
            total: () => tree?.siblingsOf(props.stat).length ?? 0,
            canMoveUp: () => !!tree?.canMoveUp(props.stat),
            canMoveDown: () => !!tree?.canMoveDown(props.stat),
            canIndent: () => !!tree?.canIndent(props.stat),
            canOutdent: () => !!tree?.canOutdent(props.stat),
            onMove: (from, to) => tree?.move(props.stat, to > from ? 'down' : 'up'),
            onIndent: () => tree?.move(props.stat, 'in'),
            onOutdent: () => tree?.move(props.stat, 'out'),
            positionKey: () => (tree ? tree.positionKey(props.stat) : ''),
            positionMessage: () =>
                __('messages.keyboard_tree_reorder_position', {
                    level: props.stat.level,
                    current: (tree?.indexOf(props.stat) ?? 0) + 1,
                    total: tree?.siblingsOf(props.stat).length ?? 0,
                }),
            instructions: () => __('messages.keyboard_tree_reorder_instructions'),
        });

        return { canKeyboardMove, moving, reorderFocusEl, reorderStatus, startMoving, moveOrigin };
    },

    data() {
        return {
            editing: false,
        };
    },

    computed: {
        isTopLevelBranch() {
            return this.depth === 1;
        },

        isRoot() {
            return this.root;
        },

        isEntry() {
            return Boolean(this.page.id);
        },

        isLink() {
            return !this.page.id && this.page.title && this.page.url;
        },

        isText() {
            return this.page.title && !this.page.url;
        },

        title() {
            return this.page.title || this.page.entry_title || this.page.url;
        },

        slugPath() {
            return this.isRoot ? '/' : '/' + this.page.slug;
        },
    },

    methods: {
        getStatusTooltip() {
            let label = __(this.page.status) || __('Text item');

            return label[0].toUpperCase() + label.slice(1);
        },

        remove(deleteChildren) {
            this.$emit('removed', this.stat, deleteChildren);
        },
    },
};
</script>
