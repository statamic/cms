<template>
    <div class="page-tree-branch flex" :class="{ 'ml-[-24px]': isTopLevel }">
        <div class="page-move w-6" />
        <div class="flex flex-1 items-center p-2 text-xs leading-normal ltr:ml-2 rtl:mr-2">
            <div class="flex flex-1 items-center" :class="{ 'opacity-50': isHidden || isInHiddenSection }">
                <template v-if="!isSection && !isChild">
                    <i v-if="isAlreadySvg" class="h-4 w-4 ltr:mr-2 rtl:ml-2" v-html="icon"></i>
                    <Icon v-else class="h-4 w-4 ltr:mr-2 rtl:ml-2" :name="icon" />
                </template>

                <a
                    @click="$emit('edit', $event)"
                    :class="{ 'text-sm font-medium': isSection }"
                    v-text="__(item.text)"
                />

                <button
                    v-if="hasChildren && !isSection"
                    class="dark:hover:dark-text-150 flex p-2 text-gray-600 outline-hidden transition duration-100 hover:text-gray-700 dark:text-dark-200"
                    :class="{ '-rotate-90': !isOpen }"
                    @click="$emit('toggle-open')"
                >
                    <svg-icon name="micro/chevron-down-xs" class="h-1.5" />
                </button>
            </div>

            <div class="flex items-center ltr:pr-2 rtl:pl-2">
                <slot name="branch-icon" :branch="item" />

                <svg-icon
                    v-if="isRenamedSection"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/content-writing"
                    v-tooltip="__('Renamed Section')"
                />
                <svg-icon
                    v-else-if="isHidden"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/hidden"
                    v-tooltip="isSection ? __('Hidden Section') : __('Hidden Item')"
                />
                <svg-icon
                    v-else-if="isPinnedAlias"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/pin"
                    v-tooltip="__('Pinned Item')"
                />
                <svg-icon
                    v-else-if="isAlias"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/duplicate-ids"
                    v-tooltip="__('Alias Item')"
                />
                <svg-icon
                    v-else-if="isMoved"
                    class="inline-block w-4 text-gray-500"
                    name="regular/flip-vertical"
                    v-tooltip="__('Moved Item')"
                />
                <svg-icon
                    v-else-if="isModified"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/content-writing"
                    v-tooltip="__('Modified Item')"
                />
                <svg-icon
                    v-else-if="isCustom"
                    class="inline-block w-4 text-gray-500"
                    name="light/user-edit"
                    v-tooltip="isSection ? __('Custom Section') : __('Custom Item')"
                />

                <Dropdown placement="left-start" class="me-4">
                    <DropdownMenu>
                        <slot
                            name="branch-options"
                            :item="item"
                            :depth="depth"
                            :remove-branch="remove"
                            :is-top-level="isTopLevel"
                        />
                    </DropdownMenu>
                </Dropdown>
            </div>
        </div>
    </div>
</template>

<script>
import { data_get } from '../../bootstrap/globals.js';
import { Icon, Dropdown, DropdownMenu } from '@statamic/ui';

export default {
    components: {
        Icon,
        Dropdown,
        DropdownMenu,
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
            return data_get(this.item, 'manipulations.action') === '@alias' && this.isTopLevel;
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

        isTopLevel() {
            return this.stat.data?.text === 'Top Level' || this.stat.parent?.data?.text === 'Top Level';
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
