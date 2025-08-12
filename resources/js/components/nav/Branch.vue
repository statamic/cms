<template>
    <div class="page-tree-branch flex" :class="{
        'ml-[-24px]': inTopLevelSection,
        'page-tree-branch--has-children': hasChildren,
    }">
        <div class="page-move w-6" />
        <div class="flex flex-1 items-center p-1.5 text-xs leading-normal">
            <div class="flex gap-3 grow items-center" :class="{ 'opacity-50': isHidden || isInHiddenSection }">
                <template v-if="!isSection && !isChild">
                    <i v-if="isAlreadySvg" class="size-4" v-html="icon"></i>
                    <Icon v-else class="size-4" :name="icon" />
                </template>

                <a
                    @click="$emit('edit', $event)"
                    :class="{ 'text-sm font-medium is-section': isSection }"
                    v-text="__(item.text)"
                />

                <Button
                    v-if="hasChildren && !isSection"
                    class="transition duration-100 [&_svg]:size-4! -mx-1.5"
                    icon="ui/chevron-down"
                    size="xs"
                    round
                    variant="ghost"
                    :class="{ '-rotate-90 is-closed': !isOpen, 'is-open': isOpen }"
                    @click="$emit('toggle-open')"
                />
            </div>

            <div class="flex items-center gap-3">
                <slot name="branch-icon" :branch="item" />
                <ui-icon
                    v-if="isRenamedSection"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="fieldsets"
                    v-tooltip="__('Renamed Section')"
                />
                <ui-icon
                    v-else-if="isHidden"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="eye-closed"
                    v-tooltip="isSection ? __('Hidden Section') : __('Hidden Item')"
                />
                <ui-icon
                    v-else-if="isPinnedAlias"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="pin"
                    v-tooltip="__('Pinned Item')"
                />
                <ui-icon
                    v-else-if="isAlias"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="duplicate"
                    v-tooltip="__('Aliased Item')"
                />
                <ui-icon
                    v-else-if="isMoved"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="moved"
                    v-tooltip="__('Moved Item')"
                />
                <ui-icon
                    v-else-if="isModified"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="fieldsets"
                    v-tooltip="__('Modified Item')"
                />
                <ui-icon
                    v-else-if="isCustom"
                    class="size-4 text-gray-400 dark:text-gray-600"
                    name="user-edit"
                    v-tooltip="isSection ? __('Custom Section') : __('Custom Item')"
                />

                <Dropdown placement="left-start">
                    <DropdownMenu>
                        <slot
                            name="branch-options"
                            :item="item"
                            :depth="depth"
                            :remove-branch="remove"
                            :in-top-level-section="inTopLevelSection"
                        />
                    </DropdownMenu>
                </Dropdown>
            </div>
        </div>
    </div>
</template>

<script>
import { data_get } from '../../bootstrap/globals.js';
import { Icon, Dropdown, DropdownMenu, Button } from '@statamic/ui';

export default {
    components: {
        Icon,
        Dropdown,
        DropdownMenu,
        Button,
    },

    props: {
        item: Object,
        parentSection: Object,
        depth: Number,
        root: Boolean,
        stat: Object,
        isOpen: Boolean,
        isChild: Boolean,
        hasChildren: Boolean,
        disableSections: Boolean,
    },

    data() {
        return {
            editing: false,
        };
    },

    computed: {
        isSection() {
            if (this.disableSections) {
                return false;
            }

            return this.depth === 1;
        },

        title() {
            return this.item.title || this.item.entry_title || this.item.url;
        },

        icon() {
            return data_get(this.item, 'config.icon') || data_get(this.item, 'original.icon') || 'entries';
        },

        isAlreadySvg() {
            return this.icon.startsWith('<svg');
        },

        isRenamedSection() {
            return this.isSection && this.item.text !== data_get(this.item, 'config.display_original');
        },

        isHidden() {
            return data_get(this.item, 'manipulations.action') === '@hide';
        },

        isInHiddenSection() {
            return this.parentSection && this.parentSection.data.manipulations.action === '@hide';
        },

        isPinnedAlias() {
            return data_get(this.item, 'manipulations.action') === '@alias' && this.inTopLevelSection;
        },

        isAlias() {
            return data_get(this.item, 'manipulations.action') === '@alias';
        },

        isMoved() {
            return data_get(this.item, 'manipulations.action') === '@move';
        },

        isModified() {
            return data_get(this.item, 'manipulations.action') === '@modify';
        },

        isCustom() {
            return data_get(this.item, 'manipulations.action') === '@create';
        },

        inTopLevelSection() {
            return this.parentSection?.data?.text === 'Top Level';
        },
    },

    methods: {
        remove() {
            const store = this.item._vm.store;
            store.deleteNode(this.item);
            this.$emit('removed', store);
        },
    },
};
</script>
