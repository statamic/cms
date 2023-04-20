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
                            {{ __('Add Nav Item') }}
                            <svg-icon name="chevron-down-xs" class="w-2 ml-2" v-if="hasCollections" />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="linkPage()" />
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
            @saved="treeSaved"
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
                    :text="__('Add child nav item')"
                    @click="linkPage(vm)" />
                <dropdown-item
                    v-if="depth < maxDepth && hasCollections"
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
            :handle="handle"
            @publish-info-updated="updatePublishInfo"
            @localized-fields-updated="updateLocalizedFields"
            @closed="closePageEditor"
            @submitted="updatePage"
        />

        <page-editor
            v-if="creatingPage"
            creating
            :site="site"
            :blueprint="blueprint"
            :handle="handle"
            @publish-info-updated="updatePendingCreatedPagePublishInfo"
            @localized-fields-updated="updatePendingCreatedPageLocalizedFields"
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
import uniqid from 'uniqid';

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
                return _.pick(value, ['entry', 'values', 'localizedFields', 'new']);
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
                id: uniqid(),
                entry: page.id,
                entry_title: page.title,
                title: null
            }));

            pages.forEach(page => {
                this.publishInfo = {...this.publishInfo, [page.id]: {
                    entry: page.entry,
                    new: true,
                }};
            });

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
            this.creatingPage = { info: null };
        },

        closePageCreator() {
            this.creatingPage = false;
        },

        pageCreated(values) {
            const page = {
                id: uniqid(),
                title: values.title,
                url: values.url,
                children: []
            };

            this.$set(this.publishInfo, page.id, {
                ...this.creatingPage.info,
                values,
                entry: null,
                new: true,
            });

            this.$refs.tree.addPages([page], this.targetParent);

            this.closePageCreator();
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

        updatePendingCreatedPagePublishInfo(info) {
            this.creatingPage.info = info;
        },

        updateLocalizedFields(fields) {
            this.publishInfo[this.editingPage.page.id].localizedFields = fields;
        },

        updatePendingCreatedPageLocalizedFields(fields) {
            this.creatingPage.info.localizedFields = fields;
        },

        treeSaved(response) {
            this.changed = false;

            this.replaceGeneratedIds(response.data.generatedIds);
        },

        replaceGeneratedIds(ids) {
            for (let [oldId, newId] of Object.entries(ids)) {
                // Replace the ID in the publishInfo so if the tree is saved again, its
                // data will be submitted using the real ID, and now the temp JS one.
                this.$set(this.publishInfo, newId, { ...this.publishInfo[oldId], new: false });
                this.$delete(this.publishInfo, oldId);

                // Replace the ID in the branch within the tree.
                // Same as above, but in the tree itself.
                let branch = this.$refs.tree.getNodeByBranchId(oldId);
                branch.id = newId;
                this.$refs.tree.pageUpdated(branch._vm.store);
            }
        }

    }

}
</script>
