<template>
    <div>
        <header class="mb-6" v-if="mounted">
            <breadcrumb :url="breadcrumbUrl" :title="__('Navigation')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="__(title)" />

                <dropdown-list v-if="canEdit" class="ltr:mr-2 rtl:ml-2">
                    <slot name="twirldown" />
                </dropdown-list>

                <a
                    @click="$refs.tree.cancel"
                    class="text-2xs text-blue underline ltr:mr-4 rtl:ml-4"
                    v-if="isDirty"
                    v-text="__('Discard changes')"
                />

                <site-selector
                    v-if="sites.length > 1"
                    class="ltr:mr-4 rtl:ml-4"
                    :sites="sites"
                    :value="site"
                    @input="siteSelected"
                />

                <dropdown-list v-if="canEdit" :disabled="!hasCollections">
                    <template #trigger>
                        <button
                            class="btn"
                            :class="{ 'flex items-center ltr:pr-4 rtl:pl-4': hasCollections }"
                            @click="addLink"
                        >
                            {{ __('Add Nav Item') }}
                            <svg-icon
                                name="micro/chevron-down-xs"
                                class="w-2 ltr:ml-4 rtl:mr-4"
                                v-if="hasCollections"
                            />
                        </button>
                    </template>
                    <dropdown-item :text="__('Add Nav Item')" @click="linkPage()" />
                    <dropdown-item :text="__('Link to Entry')" @click="linkEntries()" />
                </dropdown-list>

                <button
                    v-if="canEdit"
                    class="btn-primary ltr:ml-4 rtl:mr-4"
                    :class="{ disabled: !changed }"
                    :disabled="!changed"
                    @click="$refs.tree.save"
                    v-text="__('Save Changes')"
                />
            </div>
        </header>

        <page-tree
            ref="tree"
            :pages-url="pagesUrl"
            :submit-url="submitUrl"
            :submit-parameters="{ data: submissionData }"
            :max-depth="maxDepth"
            :expects-root="expectsRoot"
            :site="site"
            :preferences-prefix="preferencesPrefix"
            :editable="canEdit"
            @edit-page="editPage"
            @changed="
                changed = true;
                targetParent = null;
            "
            @saved="treeSaved"
            @canceled="changed = false"
        >
            <template #empty>
                <div class="card content w-full p-4">
                    <div class="flex w-full flex-wrap">
                        <a
                            :href="editUrl"
                            class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
                        >
                            <svg-icon
                                name="light/hammer-wrench"
                                class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4"
                            />
                            <div class="mb-4 flex-1 md:mb-0 ltr:md:mr-6 rtl:md:ml-6">
                                <h3 class="mb-2 text-blue dark:text-blue-600">
                                    {{ __('Configure Navigation') }}
                                    <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                                </h3>
                                <p>{{ __('messages.navigation_configure_settings_intro') }}</p>
                            </div>
                        </a>
                        <a
                            @click="linkPage()"
                            class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
                        >
                            <svg-icon
                                name="paperclip"
                                class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4"
                            />
                            <div class="mb-4 flex-1 md:mb-0 ltr:md:mr-6 rtl:md:ml-6">
                                <h3 class="mb-2 text-blue dark:text-blue-600">
                                    {{ __('Link to URL') }}
                                    <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                                </h3>
                                <p>{{ __('messages.navigation_link_to_url_instructions') }}</p>
                            </div>
                        </a>
                        <a
                            @click="linkEntries()"
                            v-if="hasCollections"
                            class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
                        >
                            <svg-icon
                                name="light/hierarchy-files"
                                class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4"
                            />
                            <div class="mb-4 flex-1 md:mb-0 ltr:md:mr-6 rtl:md:ml-6">
                                <h3 class="mb-2 text-blue dark:text-blue-600">
                                    {{ __('Link to Entry') }}
                                    <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                                </h3>
                                <p>{{ __('messages.navigation_link_to_entry_instructions') }}</p>
                            </div>
                        </a>
                        <a
                            :href="docs_url('navigation')"
                            class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
                        >
                            <svg-icon
                                name="light/book-pages"
                                class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4"
                            />
                            <div class="mb-4 flex-1 md:mb-0 ltr:md:mr-6 rtl:md:ml-6">
                                <h3 class="mb-2 text-blue dark:text-blue-600">
                                    {{ __('Read the Documentation') }}
                                    <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                                </h3>
                                <p>{{ __('messages.navigation_documentation_instructions') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </template>

            <template #branch-icon="{ branch }">
                <svg-icon
                    v-if="isEntryBranch(branch)"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/hyperlink"
                    v-tooltip="__('Entry link')"
                />
                <svg-icon
                    v-if="isLinkBranch(branch)"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/external-link"
                    v-tooltip="__('External link')"
                />
                <svg-icon
                    v-if="isTextBranch(branch)"
                    class="inline-block h-4 w-4 text-gray-500"
                    name="light/file-text"
                    v-tooltip="__('Text')"
                />
            </template>

            <template v-if="canEdit" #branch-options="{ branch, removeBranch, stat, depth }">
                <dropdown-item v-if="isEntryBranch(stat)" :text="__('Edit Entry')" :redirect="branch.edit_url" />
                <dropdown-item :text="__('Edit nav item')" @click="editPage(branch)" />
                <dropdown-item v-if="depth < maxDepth" :text="__('Add child nav item')" @click="linkPage(stat)" />
                <dropdown-item
                    v-if="depth < maxDepth && hasCollections"
                    :text="__('Add child link to entry')"
                    @click="linkEntries(stat)"
                />
                <dropdown-item :text="__('Remove')" class="warning" @click="deleteTreeBranch(branch, removeBranch)" />
            </template>
        </page-tree>

        <page-selector
            v-if="hasCollections"
            ref="selector"
            :site="site"
            :collections="collections"
            :max-items="maxPagesSelection"
            :can-select-across-sites="canSelectAcrossSites"
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
            :read-only="!canEdit"
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
            :read-only="!canEdit"
            @publish-info-updated="updatePendingCreatedPagePublishInfo"
            @localized-fields-updated="updatePendingCreatedPageLocalizedFields"
            @closed="closePageCreator"
            @submitted="pageCreated"
        />

        <remove-page-confirmation
            v-if="showPageDeletionConfirmation"
            :children="numberOfChildrenToBeDeleted"
            @confirm="pageDeletionConfirmCallback"
            @cancel="
                showPageDeletionConfirmation = false;
                pageBeingDeleted = null;
            "
        />
    </div>
</template>

<script>
import PageEditor from '../structures/PageEditor.vue';
import PageSelector from '../structures/PageSelector.vue';
import RemovePageConfirmation from './RemovePageConfirmation.vue';
import SiteSelector from '../SiteSelector.vue';
import uniqid from 'uniqid';
import { defineAsyncComponent } from 'vue';
import { mapValues, pick } from 'lodash-es';

export default {
    components: {
        PageTree: defineAsyncComponent(() => import('../structures/PageTree.vue')),
        PageEditor,
        PageSelector,
        RemovePageConfirmation,
        SiteSelector,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        collections: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        editUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        maxDepth: { type: Number, default: Infinity },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
        sites: { type: Array, required: true },
        blueprint: { type: Object, required: true },
        canEdit: { type: Boolean, required: true },
        canSelectAcrossSites: { type: Boolean, required: true },
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
            removePageOnCancel: false,
            preferencesPrefix: `navs.${this.handle}`,
            publishInfo: {},
        };
    },

    computed: {
        isDirty() {
            return this.$dirty.has('page-tree');
        },

        numberOfChildrenToBeDeleted() {
            let children = 0;
            const countChildren = (page) => {
                page.children.forEach((child) => {
                    children++;
                    countChildren(child);
                });
            };
            countChildren(this.pageBeingDeleted);
            return children;
        },

        hasCollections() {
            return this.collections.length > 0;
        },

        submissionData() {
            return mapValues(this.publishInfo, (value) => {
                return pick(value, ['entry', 'values', 'localizedFields', 'new']);
            });
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },

        fields() {
            return this.blueprint.tabs.reduce((fields, tab) => {
                return tab.sections.reduce((fields, section) => {
                    return fields.concat(section.fields);
                }, []);
            }, []);
        },

        maxPagesSelection() {
            if (this.fields.filter((field) => field.validate?.includes('required')).length > 0) {
                return 1;
            }

            return;
        },
    },

    watch: {
        changed(changed) {
            this.$dirty.state('page-tree', changed);
        },
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
            pages = pages.map((page) => ({
                ...page,
                id: uniqid(),
                entry: page.id,
                entry_title: page.title,
                title: null,
            }));

            pages.forEach((page) => {
                this.publishInfo = {
                    ...this.publishInfo,
                    [page.id]: {
                        entry: page.entry,
                        new: true,
                    },
                };
            });

            this.$refs.tree.addPages(pages, this.targetParent);

            if (this.maxPagesSelection === 1) {
                this.removePageOnCancel = true;
                this.$wait(300).then(() => this.editPage(pages[0]));
            }
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

        editPage(page) {
            this.editingPage = { page };
        },

        updatePage(values) {
            this.editingPage.page.url = values.url;
            this.editingPage.page.title = values.title;
            this.editingPage.page.values = values;
            this.$refs.tree.pageUpdated();
            this.publishInfo[this.editingPage.page.id].values = values;

            this.editingPage = false;
        },

        closePageEditor() {
            if (this.removePageOnCancel) {
                this.$refs.tree.$refs[`branch-${this.editingPage.page.id}`].remove();
                this.removePageOnCancel = false;
            }

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
            };

            this.publishInfo[page.id] = {
                ...this.creatingPage.info,
                values,
                entry: null,
                new: true,
            };

            this.$refs.tree.addPages([page], this.targetParent);

            this.closePageCreator();
        },

        deleteTreeBranch(branch, removeFromUi) {
            this.showPageDeletionConfirmation = true;
            this.pageBeingDeleted = branch;
            this.pageDeletionConfirmCallback = (shouldDeleteChildren) => {
                removeFromUi(shouldDeleteChildren);
                this.showPageDeletionConfirmation = false;
                this.pageBeingDeleted = branch;
            };
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
            if (!response.data.saved) {
                return this.$toast.error(`Couldn't save tree`);
            }

            this.replaceGeneratedIds(response.data.generatedIds);

            this.changed = false;
        },

        replaceGeneratedIds(ids) {
            for (let [oldId, newId] of Object.entries(ids)) {
                // Replace the ID in the publishInfo so if the tree is saved again, its
                // data will be submitted using the real ID, and now the temp JS one.
                this.publishInfo[newId] = { ...this.publishInfo[oldId], new: false };
                delete this.publishInfo[oldId];

                // Replace the ID in the branch within the tree.
                // Same as above, but in the tree itself.
                let branch = this.$refs.tree.getNodeByBranchId(oldId);
                branch.id = newId;
                this.$refs.tree.pageUpdated();
            }
        },
    },
};
</script>
