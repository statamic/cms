<template>

    <div class="flex" :class="{ 'mb-1': isRoot }">
        <div class="page-move w-6" />

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div class="flex items-center flex-1">
                <i v-if="isRoot" class="icon icon-home mr-1 opacity-25" />

                <a v-if="!page.id" @click="edit" :class="{ 'text-sm font-medium': isTopLevel }">{{ page.title || page.url }}</a>
                <a v-else :href="page.edit_url" :class="{ 'text-sm font-medium': isTopLevel }">{{ page.title || page.url }}</a>

                <div v-if="page.collection" class="ml-2 flex items-center">
                    <svg-icon name="content-writing" class="w-4 h-4" />
                    <div class="ml-sm">
                        <a :href="page.collection.create_url">Add</a>
                        <span class="text-grey">or</span>
                        <a :href="page.collection.edit_url">Edit</a>
                    </div>
                </div>
            </div>

            <div class="pr-1 flex items-center">
                <svg-icon v-if="isEntry" class="inline-block w-4 h-4 text-grey-50" name="hyperlink" v-tooltip="__('Entry link')" />
                <svg-icon v-if="isLink" class="inline-block w-4 h-4 text-grey-50" name="external-link" v-tooltip="__('External link')" />
                <svg-icon v-if="isText" class="inline-block w-4 h-4 text-grey-50" name="file-text" v-tooltip="__('Text')" />

                <dropdown-list class="ml-2">
                    <dropdown-item :text="__('Add child link to URL')" @click="$emit('link-page')" />
                    <dropdown-item :text="__('Add child link to entry')" @click="$emit('link-entries')" />
                    <dropdown-item :text="__('Create Entry')" @click="$emit('create-entry', page.id)" />
                    <dropdown-item :text="__('Remove')" class="warning" @click="remove" />
                </dropdown-list>
            </div>

        </div>

        <page-editor
            v-if="editing"
            :initial-title="page.title"
            :initial-url="page.url"
            @closed="closeEditor"
            @submitted="updatePage"
        />
    </div>

</template>

<script>
import PageEditor from './PageEditor.vue';

export default {

    components: {
        PageEditor,
    },

    props: {
        page: Object,
        depth: Number,
        root: Boolean,
        vm: Object,
        firstPageIsRoot: Boolean,
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
        }

    },

    methods: {

        remove() {
            let message = 'This will only remove the references (and any children) from the tree. No entries will be deleted.';

            if (! confirm(message)) return;

            const store = this.page._vm.store;
            store.deleteNode(this.page);
            this.$emit('removed', store);
        },

        edit() {
            this.editing = true;
        },

        closeEditor() {
            this.editing = false;
        },

        updatePage(page) {
            this.page.url = page.url;
            this.page.title = page.title;
            this.$emit('updated');
            this.closeEditor();
        }


    }

}
</script>
