<template>

    <div class="flex" :class="{ 'mb-1': isRoot }">
        <div class="page-move w-6" />

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div
                class="flex-1"
                :class="{ 'text-sm font-medium': isTopLevel }"
            >
                <i v-if="isRoot" class="icon icon-home mr-1 opacity-25" />
                <a @click="edit">{{ page.title || page.url }}</a>
            </div>

            <div class="pr-1">
                <dropdown-list>
                    <dropdown-item :text="__('Create Page')" @click="$emit('create-page')" />
                    <dropdown-item :text="__('Create Page from Entry')" @click="$emit('create-entry')" />
                    <dropdown-item :text="__('Delete')" class="warning" @click="remove" />
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
            if (this.page.id) {
                window.location = this.page.edit_url;
                return;
            }

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
