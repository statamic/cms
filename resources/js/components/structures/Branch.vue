<template>
    <div class="page-tree-branch flex" :class="{ 'page-tree-branch--has-children': hasChildren }">
        <slot name="branch-action" :branch="page">
            <div v-if="editable" class="page-move w-6" />
        </slot>
        <div class="flex flex-1 items-center p-1.5 text-xs leading-normal">
            <div class="flex gap-3 grow items-center" @click="$emit('branch-clicked', page)">
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
                    class="transition duration-100 [&_svg]:size-4! -mx-1.5"
                    icon="ui/chevron-down"
                    size="xs"
                    variant="ghost"
                    :class="{ '-rotate-90 is-closed': !isOpen, 'is-open': isOpen }"
                    :aria-label="isOpen ? __('Collapse') : __('Expand')"
                    :aria-expanded="isOpen"
                    @click.stop="$emit('toggle-open')"
                />

                <div v-if="page.collection && editable" class="flex items-center gap-2">
                    <Icon name="navigation" class="size-3.5 text-gray-500" />
                    <div>
                        <a :href="page.collection.create_url" v-text="__('Add')" class="hover:text-blue-500" />
                        <span class="mx-1 text-gray-400 dark:text-gray-500">/</span>
                        <a :href="page.collection.edit_url" v-text="__('Edit')" class="hover:text-blue-500" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <ui-badge
                    v-if="showBlueprint && page.entry_blueprint"
                    :text="__(page.entry_blueprint.title)"
                    size="sm"
                    variant="filled"
                    v-tooltip="__('Blueprint')"
                />

                <slot name="branch-icon" :branch="page" />

                <Dropdown placement="left-start" :class="{ invisible: isRoot, hidden: !editable }">
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
        showBlueprint: Boolean,
        showSlugs: Boolean,
        editable: { type: Boolean, default: true },
        stat: Object,
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
