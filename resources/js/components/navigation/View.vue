<template>
    <div>
        <Header v-if="mounted" :title="title" icon="navigation">
            <Dropdown placement="left-start">
                <DropdownMenu>
                    <DropdownItem v-if="canEdit" :text="__('Configure Navigation')" icon="cog" :href="editUrl" />
                    <DropdownItem v-if="canEditBlueprint" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="blueprintUrl" />
                </DropdownMenu>
            </Dropdown>

            <ui-button
                v-if="isDirty"
                variant="filled"
                :text="__('Discard changes')"
                @click="$refs.tree.cancel"
            />

            <site-selector
                v-if="sites.length > 1"
                :sites="sites"
                :value="site"
                @input="siteSelected"
            />

            <Dropdown v-if="canEdit && hasCollections" placement="left-start" :disabled="!hasCollections">
                <template #trigger>
                    <Button
                        :text="__('Add')"
                        icon-append="ui/chevron-down"
                    />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Add Nav Item')"
                        @click="linkPage()"
                        icon="add-list"
                    />
                    <DropdownItem
                        :text="__('Link to Entry')"
                        @click="linkEntries()"
                        icon="add-link"
                    />
                </DropdownMenu>
            </Dropdown>

            <Button
                v-else-if="canEdit && !hasCollections"
                :text="__('Add Nav Item')"
                @click="addLink"
            />

            <Button
                v-if="canEdit"
                :disabled="!changed"
                variant="primary"
                :text="__('Save Changes')"
                @click="$refs.tree?.save"
            />
        </Header>

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
                <EmptyStateMenu :heading="__('Start designing your navigation with these steps')">
                    <EmptyStateItem
                        :href="editUrl"
                        icon="configure"
                        :heading="__('Configure Navigation')"
                        :description="__('messages.navigation_configure_settings_intro')"
                    />

                    <EmptyStateItem
                        icon="fieldtype-link"
                        :heading="__('Link to URL')"
                        :description="__('messages.navigation_link_to_url_instructions')"
                        @click="linkPage"
                    />

                    <EmptyStateItem
                        v-if="hasCollections"
                        icon="navigation"
                        :heading="__('Link to Entry')"
                        :description="__('messages.navigation_link_to_entry_instructions')"
                        @click="linkEntries()"
                    />

                    <EmptyStateItem
                        :href="docs_url('navigation')"
                        icon="support"
                        :heading="__('Read the Documentation')"
                        :description="__('messages.navigation_documentation_instructions')"
                    />
                </EmptyStateMenu>
            </template>

            <template #branch-icon="{ branch }">
                <ui-tooltip v-if="isEntryBranch(branch)" :text="__('Entry link')">
                    <ui-icon class="size-3.5! text-gray-500" name="link" />
                </ui-tooltip>
                <ui-tooltip v-if="isLinkBranch(branch)" :text="__('External link')">
                    <ui-icon class="size-3.5! text-gray-500" name="external-link" />
                </ui-tooltip>
                <ui-tooltip v-if="isTextBranch(branch)" :text="__('Text')">
                    <ui-icon class="size-3.5! text-gray-500" name="page" />
                </ui-tooltip>
            </template>

            <template v-if="canEdit" #branch-options="{ branch, removeBranch, stat, depth }">
                <DropdownItem
                    v-if="isEntryBranch(stat)"
                    :text="__('Edit Entry')"
                    :href="branch.edit_url"
                    icon="edit"
                />
                <DropdownItem
                    :text="__('Edit Nav item')"
                    @click="editPage(branch)"
                    icon="edit"
                />
                <DropdownItem
                    v-if="depth < maxDepth"
                    :text="__('Add child nav item')"
                    @click="linkPage(stat)"
                    icon="add-list"
                />
                <DropdownItem
                    v-if="depth < maxDepth && hasCollections"
                    :text="__('Add child link to entry')"
                    @click="linkEntries(stat)"
                    icon="add-link"
                />
                <DropdownSeparator />
                <DropdownItem
                    :text="__('Remove')"
                    variant="destructive"
                    @click="deleteTreeBranch(branch, removeBranch)"
                    icon="trash"
                />
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
import { Dropdown, DropdownMenu, DropdownItem, DropdownSeparator, Button, EmptyStateMenu, EmptyStateItem, Header } from '@statamic/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        PageTree: defineAsyncComponent(() => import('../structures/PageTree.vue')),
        PageEditor,
        PageSelector,
        RemovePageConfirmation,
        SiteSelector,
        EmptyStateMenu,
        EmptyStateItem,
        Header,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        collections: { type: Array, required: true },
        editUrl: { type: String, required: true },
        blueprintUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        maxDepth: { type: Number, default: Infinity },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
        sites: { type: Array, required: true },
        blueprint: { type: Object, required: true },
        canEdit: { type: Boolean, required: true },
        canSelectAcrossSites: { type: Boolean, required: true },
        canEditBlueprint: { type: Boolean, required: true },
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
