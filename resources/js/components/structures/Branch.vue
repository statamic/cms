<template>

    <div class="flex">
        <div class="page-move w-6" />
        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">
            <div class="flex items-center flex-1">
                <div class="little-dot mr-1" :class="getStatusClass()" v-tooltip="getStatusTooltip()" />
                <svg-icon name="home-page" class="mr-1 h-4 w-4 text-grey-80" v-if="isRoot" v-tooltip="__('This is the root page')" />
                <a
                    @click="$emit('edit', $event)"
                    :class="{ 'text-sm font-medium': isTopLevel }"
                    v-text="title" />

                <span v-if="showSlugs" class="ml-1 font-mono text-grey-70 text-2xs pt-px">
                    {{ isRoot ? '/' : page.slug }}
                </span>

                <button
                    v-if="hasChildren"
                    class="p-1 text-grey-60 hover:text-grey-70 transition duration-100 outline-none flex"
                    :class="{ '-rotate-90': !isOpen }"
                    @click="$emit('toggle-open')"
                >
                    <svg-icon name="chevron-down-xs" class="h-2.5" />
                </button>

                <div v-if="page.collection" class="ml-2 flex items-center">
                    <svg-icon name="content-writing" class="w-4 h-4" />
                    <div class="ml-sm">
                        <a :href="page.collection.create_url" v-text="__('Add')" />
                        <span class="text-grey">/</span>
                        <a :href="page.collection.edit_url" v-text="__('Edit')" />
                    </div>
                </div>
            </div>

            <div class="pr-1 flex items-center">
                <slot name="branch-icon" :branch="page" />

                <dropdown-list class="ml-2" v-if="!isRoot">
                    <slot name="branch-options"
                        :branch="page"
                        :depth="depth"
                        :remove-branch="remove"
                        :orphan-children="orphanChildren"
                    />
                </dropdown-list>
            </div>

        </div>
    </div>

</template>

<script>
import * as th from 'tree-helper';

export default {

    props: {
        page: Object,
        depth: Number,
        root: Boolean,
        vm: Object,
        firstPageIsRoot: Boolean,
        hasCollection: Boolean,
        isOpen: Boolean,
        hasChildren: Boolean,
        showSlugs: Boolean
    },

    data() {
        return {
            editing: false,
        }
    },

    computed: {

        isTopLevel() {
            return this.depth === 1;
        },

        isRoot() {
            if (!this.firstPageIsRoot) return false;
            if (!this.isTopLevel) return false;

            const firstNodeId = this.vm.data.parent.children[0].id;
            return this.page.id === firstNodeId;
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
        }

    },

    methods: {

        getStatusClass() {
            switch (this.page.status) {
                case 'published':
                    return 'bg-green';
                case 'draft':
                    return 'bg-grey-40';
                default:
                    return 'bg-transparent border border-grey-60';
            }
        },

        getStatusTooltip() {
            let label = __(this.page.status) || __('Text item');

            return label[0].toUpperCase() + label.slice(1);
        },

        remove() {
            const store = this.page._vm.store;
            store.deleteNode(this.page);
            this.$emit('removed', store);
        },

        orphanChildren() {
            const store = this.page._vm.store;

            this.vm.data.children.slice().forEach((child) =>
                th.insertBefore(child, this.vm.data)
            );

            this.$emit('children-orphaned', store);
        }

    }

}
</script>
