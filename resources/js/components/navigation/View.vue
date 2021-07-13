<template>

    <div>

        <header class="mb-3" v-if="mounted">
            <breadcrumb :url="breadcrumbUrl" :title="__('Navigation')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <slot name="twirldown" />
                </dropdown-list>

                <a @click="$refs.tree.cancel" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <site-selector
                    v-if="sites.length > 1"
                    class="mr-2"
                    :sites="sites"
                    :value="site"
                    @input="siteSelected"
                />

                <dropdown-list :disabled="! hasCollections">
                    <template #trigger>
                        <button
                            class="btn"
                            :class="{ 'flex items-center pr-2': hasCollections }"
                            @click="addLink"
                        >
                            {{ __('Add Link') }}
                            <svg-icon name="chevron-down-xs" class="w-2 ml-2" v-if="hasCollections" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Link to URL')" @click="linkPage()" />
                    <dropdown-item :text="__('Link to Entry')" @click="linkEntries()" />
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
            :submit-parameters="{ data: submissionData }"
            :max-depth="maxDepth"
            :expects-root="expectsRoot"
            :site="site"
            :preferences-prefix="preferencesPrefix"
            @edit-page="editPage"
            @changed="changed = true; targetParent = null;"
            @saved="changed = false"
            @canceled="changed = false"
        >
            <template #empty>
                <div class="card p-2 content w-full">
                    <div class="flex flex-wrap w-full">
                        <a :href="editUrl" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
                            <svg-icon name="hammer-wrench" class="h-8 w-8 mr-2 text-grey-80" />
                            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                                <h3 class="mb-1 text-blue">{{ __('Configure Navigation') }} &rarr;</h3>
                                <p>{{ __('messages.navigation_configure_settings_intro') }}</p>
                            </div>
                        </a>
                        <a @click="linkPage()" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
                            <svg-icon name="paperclip" class="h-8 w-8 mr-2 text-grey-80" />
                            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                                <h3 class="mb-1 text-blue">{{ __('Link to URL') }} &rarr;</h3>
                                 <p>{{ __('messages.navigation_link_to_url_instructions') }}</p>
                            </div>
                        </a>
                        <a @click="linkEntries()" v-if="hasCollections" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
                            <svg-icon name="hierarchy-files" class="h-8 w-8 mr-2 text-grey-80" />
                            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                                <h3 class="mb-1 text-blue">{{ __('Link to Entry') }} &rarr;</h3>
                                 <p>{{ __('messages.navigation_link_to_entry_instructions') }}</p>
                            </div>
                        </a>
                        <a :href="docs_url('navigation')" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
                            <svg-icon name="book-pages" class="h-8 w-8 mr-2 text-grey-80" />
                            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                                <h3 class="mb-1 text-blue">{{ __('Read the Documentation') }} &rarr;</h3>
                                 <p>{{ __('messages.navigation_documentation_instructions') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </template>

            <template #branch-icon="{ branch }">
                <svg-icon v-if="isEntryBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="hyperlink" v-tooltip="__('Entry link')" />
                <svg-icon v-if="isLinkBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="external-link" v-tooltip="__('External link')" />
                <svg-icon v-if="isTextBranch(branch)" class="inline-block w-4 h-4 text-grey-50" name="file-text" v-tooltip="__('Text')" />
            </template>

            <template #branch-options="{ branch, removeBranch, orphanChildren, vm, depth }">
                <dropdown-item
                    v-if="isEntryBranch(branch)"
                    :text="__('Edit Entry')"
                    :redirect="branch.edit_url" />
                <dropdown-item
                    v-if="depth < maxDepth"
                    :text="__('Add child link to URL')"
                    @click="linkPage(vm)" />
                <dropdown-item
                    v-if="depth < maxDepth"
                    :text="__('Add child link to entry')"
                    @click="linkEntries(vm)" />
                <dropdown-item
                    :text="__('Remove')"
                    class="warning"
                    @click="deleteTreeBranch(branch, removeBranch, orphanChildren)" />
            </template>
        </page-tree>

        <page-selector
            v-if="hasCollections && $refs.tree"
            ref="selector"
            :site="site"
            :collections="collections"
            @selected="entriesSelected"
        />

        <page-editor
            v-if="editingPage"
            :site="site"
            :id="editingPage.page.id"
            :entry="editingPage.page.entry"
            :editEntryUrl="editingPage.page.entry ? editingPage.page.edit_url : null"
            :publish-info="publishInfo[editingPage.page.id]"
            :blueprint="blueprint"
            @publish-info-updated="updatePublishInfo"
            @localized-fields-updated="updateLocalizedFields"
            @closed="closePageEditor"
            @submitted="updatePage"
        />

        <page-editor
            v-if="creatingPage"
            :site="site"
            :blueprint="blueprint"
            @closed="closePageCreator"
            @submitted="pageCreated"
        />

        <remove-page-confirmation
            v-if="showPageDeletionConfirmation"
            :children="numberOfChildrenToBeDeleted"
            @confirm="pageDeletionConfirmCallback"
            @cancel="showPageDeletionConfirmation = false; pageBeingDeleted = null;"
        />

    </div>

</template>

<script>
import PageTree from '../structures/PageTree.vue';
import PageEditor from '../structures/PageEditor.vue';
import PageSelector from '../structures/PageSelector.vue';
import RemovePageConfirmation from './RemovePageConfirmation.vue';
import SiteSelector from '../SiteSelector.vue';

export default {

    components: {
        PageTree,
        PageEditor,
        PageSelector,
        RemovePageConfirmation,
        SiteSelector
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        collections: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        editUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        maxDepth: { type: Number, default: Infinity, },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
        sites: { type: Array, required: true },
        blueprint: { type: Object, required: true }
    },

    data() {
        return {
            mounted: false,
            changed: false,
            creatingPage: false,
            editingPage: false,
            targetParent: null,
            showPageDeletionConfirmation: false,
            pageBeingDeleted: null,
            pageDeletionConfirmCallback: null,
            preferencesPrefix: `navs.${this.handle}`,
            publishInfo: {},
        }
    },

    computed: {

        isDirty() {
            return this.$dirty.has('page-tree');
        },

        numberOfChildrenToBeDeleted() {
            let children = 0;
            const countChildren = (page) => {
                page.children.forEach(child => {
                    children++;
                    countChildren(child);
                });
            }
            countChildren(this.pageBeingDeleted);
            return children;
        },

        hasCollections() {
            return this.collections.length > 0;
        },

        submissionData() {
            return _.mapObject(this.publishInfo, value => {
                return _.pick(value, ['entry', 'values', 'localizedFields']);
            });
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
            if (!this.hasCollections) this.linkPage();
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
            pages = pages.map(page => ({
                ...page,
                entry_title: page.title,
                title: null
            }));

            this.$refs.tree.addPages(pages, this.targetParent);
        },

        isEntryBranch(branch) {
            return !!branch.entry;
        },

        isLinkBranch(branch) {
            return !this.isEntryBranch(branch) && branch.url;
        },

        isTextBranch(branch) {
            return !this.isEntryBranch(branch) && !this.isLinkBranch(branch);
        },

        editPage(page, vm, store) {
            this.editingPage = { page, vm, store };
        },

        updatePage(values) {
            this.editingPage.page.url = values.url;
            this.editingPage.page.title = values.title;
            this.editingPage.page.values = values;
            this.$refs.tree.pageUpdated(this.editingPage.store);
            this.publishInfo[this.editingPage.page.id].values = values;

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

        deleteTreeBranch(branch, removeFromUi, orphanChildren) {
            this.showPageDeletionConfirmation = true;
            this.pageBeingDeleted = branch;
            this.pageDeletionConfirmCallback = (shouldDeleteChildren) => {
                if (!shouldDeleteChildren) orphanChildren();
                removeFromUi();
                this.showPageDeletionConfirmation = false;
                this.pageBeingDeleted = branch;
            }
        },

        siteSelected(site) {
            window.location = site.url;
        },

        updatePublishInfo(info) {
            this.publishInfo = { ...this.publishInfo, [this.editingPage.page.id]: info };
        },

        updateLocalizedFields(fields) {
            this.publishInfo[this.editingPage.page.id].localizedFields = fields;
        }

    }

}
</script>
