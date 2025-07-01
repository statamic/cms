<template>
    <div class="page-tree-branch flex" :class="{ 'page-tree-branch--has-children': hasChildren }">
        <slot name="branch-action" :branch="page">
            <div v-if="editable" class="page-move w-6" />
        </slot>
        <div class="flex flex-1 items-center p-1.5 text-xs leading-normal">
            <div class="flex grow items-center" @click="$emit('branch-clicked', page)">
                <div class="little-dot ltr:mr-2 rtl:ml-2" :class="getStatusClass()" v-tooltip="getStatusTooltip()" />
                <svg-icon
                    name="home-page"
                    class="h-4 w-4 text-gray-800 dark:text-dark-150 ltr:mr-2 rtl:ml-2"
                    v-if="isRoot"
                    v-tooltip="__('This is the root page')"
                />
                <a
                    @click.prevent="$emit('edit', $event)"
                    :class="{ 'text-sm font-medium': isTopLevel }"
                    :href="page.edit_url"
                    v-text="title"
                />

                <span
                    v-if="showSlugs"
                    class="pt-px font-mono text-2xs text-gray-700 dark:text-dark-175 ltr:ml-2 rtl:mr-2"
                >
                    {{ isRoot ? '/' : page.slug }}
                </span>

                <button
                    v-if="hasChildren"
                    class="flex p-2 text-gray-600 outline-hidden transition duration-100 hover:text-gray-700 dark:text-dark-175 dark:hover:text-dark-150"
                    :class="{ '-rotate-90': !isOpen }"
                    @click.stop="$emit('toggle-open')"
                >
                    <svg-icon name="micro/chevron-down-xs" class="h-1.5" />
                </button>

                <div v-if="page.collection && editable" class="flex items-center ltr:ml-4 rtl:mr-4">
                    <Icon name="navigation" class="h-3.5 w-3.5" />
                    <div class="ms-2 ">
                        <a :href="page.collection.create_url" v-text="__('Add')" />
                        <span class="text-gray">/</span>
                        <a :href="page.collection.edit_url" v-text="__('Edit')" />
                    </div>
                </div>
            </div>

            <div class="flex items-center ltr:pr-2 rtl:pl-2">
                <div
                    v-if="showBlueprint && page.entry_blueprint"
                    v-text="__(page.entry_blueprint.title)"
                    class="ms-4 me-4 shrink font-mono lowercase text-xs text-gray-400 dark:text-dark-175"
                />

                <slot name="branch-icon" :branch="page" />

                <Dropdown placement="left-start" class="me-4" :class="{ invisible: isRoot, hidden: !editable }">
                    <DropdownMenu>
                        <slot
                            name="branch-options"
                            :branch="page"
                            :depth="depth"
                            :remove-branch="remove"
                        />
                    </DropdownMenu>
                </Dropdown>
            </div>
        </div>
    </div>
</template>

<script>
import { Dropdown, DropdownMenu, Icon } from '@statamic/ui';

export default {
    components: { Dropdown, DropdownMenu, Icon },
    props: {
        page: Object,
        depth: Number,
        root: Boolean,
        firstPageIsRoot: Boolean,
        isOpen: Boolean,
        hasChildren: Boolean,
        showSlugs: Boolean,
        showBlueprint: Boolean,
        editable: { type: Boolean, default: true },
        stat: Object,
    },

    data() {
        return {
            editing: false,
        };
    },

    computed: {
        isTopLevel() {
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
    },

    methods: {
        getStatusClass() {
            switch (this.page.status) {
                case 'published':
                    return 'bg-green-400';
                case 'draft':
                    return 'bg-gray-400 dark:bg-dark-200';
                default:
                    return 'bg-transparent border border-gray-600';
            }
        },

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
