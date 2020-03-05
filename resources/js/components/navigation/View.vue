<template>

    <div>

        <header class="mb-3" v-if="mounted">
            <breadcrumb :url="breadcrumbUrl" :title="__('Navigation')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <dropdown-item :text="__('Edit Navigation Config')" :redirect="editUrl" />
                </dropdown-list>

                <a @click="$refs.tree.cancel" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list :show-dropdown-if="collections.length > 0">
                    <template #trigger>
                        <button class="btn" v-text="__('Add Link')" @click="addLink" />
                    </template>
                    <dropdown-item :text="__('Link to URL')" @click="linkPage" />
                    <dropdown-item :text="__('Link to Entry')" @click="linkEntries" />
                </dropdown-list>

                <button
                    class="btn-primary ml-2"
                    :class="{ 'disabled': !changed }"
                    :disabled="!changed"
                    @click="$refs.tree.save"
                    v-text="__('Save Changes')" />
            </div>
        </header>

        <page-tree
            ref="tree"
            :has-collection="false"
            :pages-url="pagesUrl"
            :submit-url="submitUrl"
            :max-depth="maxDepth"
            :expects-root="expectsRoot"
            :site="site"
            @edit-page="editPage"
            @changed="changed = true; targetParent = null;"
            @saved="changed = false"
            @canceled="changed = false"
        >
            <template #branch-icon="{ branch }">
                <svg-icon v-if="isEntryBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="hyperlink" v-tooltip="__('Entry link')" />
                <svg-icon v-if="isLinkBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="external-link" v-tooltip="__('External link')" />
                <svg-icon v-if="isTextBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="file-text" v-tooltip="__('Text')" />
            </template>

            <template #branch-options="{ branch, removeBranch, vm }">
                <dropdown-item
                    :text="__('Add child link to URL')"
                    @click="linkPage(vm)" />
                <dropdown-item
                    :text="__('Add child link to entry')"
                    @click="linkEntries(vm)" />
                <dropdown-item
                    :text="__('Remove')"
                    class="warning"
                    @click="remove(removeBranch)" />
            </template>
        </page-tree>

        <page-selector
            v-if="collections.length && $refs.tree"
            ref="selector"
            :site="site"
            :collections="collections"
            @selected="entriesSelected"
        />

        <page-editor
            v-if="editingPage"
            :initial-title="editingPage.page.title"
            :initial-url="editingPage.page.url"
            @closed="closePageEditor"
            @submitted="updatePage"
        />

        <page-editor
            v-if="creatingPage"
            @closed="closePageCreator"
            @submitted="pageCreated"
        />

    </div>

</template>

<script>
import PageTree from '../structures/PageTree.vue';
import PageEditor from '../structures/PageEditor.vue';
import PageSelector from '../structures/PageSelector.vue';

export default {

    components: {
        PageTree,
        PageEditor,
        PageSelector
    },

    props: {
        title: { type: String, required: true },
        collections: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        editUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        maxDepth: { type: Number, default: Infinity, },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
    },

    data() {
        return {
            mounted: false,
            changed: false,
            creatingPage: false,
            editingPage: false,
            targetParent: null
        }
    },

    computed: {

        isDirty() {
            return this.$dirty.has('page-tree');
        }

    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
        }

    },

    mounted() {
        this.mounted = true;
    },

    methods: {

        addLink() {
            if (this.collections.length === 0) this.linkPage();
        },

        linkPage(vm) {
            this.targetParent = vm;
            this.openPageCreator();
        },

        linkEntries(vm) {
            this.targetParent = vm;
            this.$refs.selector.linkExistingItem();
        },

        entriesSelected(pages) {
            this.$refs.tree.addPages(pages, this.targetParent);
        },

        isEntryBranch(branch) {
            return !!branch.id;
        },

        isLinkBranch(branch) {
            return !this.isEntryBranch(branch) && branch.url;
        },

        isTextBranch(branch) {
            return !this.isEntryBranch(branch) && !this.isLinkBranch(branch);
        },

        editPage(page, vm, store) {
            if (page.id) {
                window.location = page.edit_url;
                return;
            }

            this.editingPage = { page, vm, store };
        },

        updatePage(page) {
            this.editingPage.page.url = page.url;
            this.editingPage.page.title = page.title;
            this.$refs.tree.pageUpdated(this.editingPage.store);

            this.editingPage = false;
        },

        closePageEditor() {
            this.editingPage = false;
        },

        openPageCreator() {
            this.creatingPage = true;
        },

        closePageCreator() {
            this.creatingPage = false;
        },

        pageCreated(page) {
            this.closePageCreator();
            this.$refs.tree.addPages([{
                title: page.title,
                url: page.url,
                children: []
            }], this.targetParent);
        },

        remove(removeBranch) {
            let message = 'This will only remove the references (and any children) from the tree. No entries will be deleted.';

            if (! confirm(message)) return;

            removeBranch();
        }

    }

}
</script>
