<script>
import { defineAsyncComponent } from 'vue';
import { mapValues, pick } from 'lodash-es';
import { nanoid as uniqid } from 'nanoid';
import Head from '@/pages/layout/Head.vue';
import PageEditor from '@/components/structures/PageEditor.vue';
import PageSelector from '@/components/structures/PageSelector.vue';
import RemovePageConfirmation from '@/components/navigation/RemovePageConfirmation.vue';
import SiteSelector from '@/components/SiteSelector.vue';
import HasActions from '@/components/publish/HasActions';
import { Dropdown, DropdownMenu, DropdownItem, DropdownSeparator, Button, EmptyStateMenu, EmptyStateItem, Header } from '@ui';
import { toggleArchitecturalBackground } from '@/pages/layout/architectural-background.js';
import { router } from '@inertiajs/vue3';
import ItemActions from '@/components/actions/ItemActions.vue';

export default {
    mixins: [HasActions],

    components: {
        Head,
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        PageTree: defineAsyncComponent(() => import('@/components/structures/PageTree.vue')),
        PageEditor,
        PageSelector,
        RemovePageConfirmation,
        SiteSelector,
        EmptyStateMenu,
        EmptyStateItem,
        Header,
        ItemActions,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        collections: { type: Array, required: true },
        editUrl: { type: String, required: true },
        blueprintUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        initialMaxDepth: { type: Number, default: null },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
        sites: { type: Array, required: true },
        blueprint: { type: Object, required: true },
        canEdit: { type: Boolean, required: true },
        canSelectAcrossSites: { type: Boolean, required: true },
        canEditBlueprint: { type: Boolean, required: true },
        entryQueryScopes: { type: Array, default: () => [] },
	    collectionTree: { type: Object, required: false },
    },

    data() {
        return {
            mounted: false,
            changed: false,
            maxDepth: this.initialMaxDepth || Infinity,
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
            if (this.fields.filter((field) => field.required).length > 0) {
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

        this.addToCommandPalette();
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
                status: null,
                children: [],
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
                delete this.publishInfo[branch.id];
            };
        },

        siteSelected(site) {
            router.get(this.sites.find((s) => s.handle === site).url);
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

        treeLoaded(pages) {
            toggleArchitecturalBackground(pages.length === 0);
        },

        treeChanged(pages) {
            this.changed = true;
            this.targetParent = null;
            toggleArchitecturalBackground(pages.length === 0);
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

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                when: () => this.canEdit,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Save Changes'),
                icon: 'save',
                action: () => this.$refs.tree?.save(),
                prioritize: true,
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit && this.hasCollections,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Add Nav Item'),
                icon: 'add-list',
                action: () => this.linkPage(),
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit && this.hasCollections,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Add Link to Entry'),
                icon: 'add-link',
                action: () => this.linkEntries(),
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit && !this.hasCollections,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Add Nav Item'),
                icon: 'add-link',
                action: () => this.addLink(),
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Configure Navigation'),
                icon: 'cog',
                url: this.editUrl,
            });

            Statamic.$commandPalette.add({
                when: () => this.canEditBlueprint,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprints'),
                icon: 'blueprint-edit',
                url: this.blueprintUrl,
            });
        },
    },
};
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="title" />

        <Header v-if="mounted" :title="title" icon="navigation">
            <ItemActions
                v-if="hasItemActions"
                :url="itemActionUrl"
                :actions="itemActions"
                :item="handle"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions: preparedActions }"
            >
                <Dropdown placement="left-start" v-if="canEdit || canEditBlueprint || hasItemActions">
                    <DropdownMenu>
                        <DropdownItem v-if="canEdit" :text="__('Configure Navigation')" icon="cog" :href="editUrl" />
                        <DropdownItem v-if="canEditBlueprint" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="blueprintUrl" />
                        <DropdownSeparator v-if="hasItemActions && (canEdit || canEditBlueprint)" />
                        <DropdownItem
                            v-for="action in preparedActions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>
            <Dropdown v-else-if="canEdit || canEditBlueprint" placement="left-start">
                <DropdownMenu>
                    <DropdownItem v-if="canEdit" :text="__('Configure Navigation')" icon="cog" :href="editUrl" />
                    <DropdownItem v-if="canEditBlueprint" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="blueprintUrl" />
                </DropdownMenu>
            </Dropdown>

            <ui-button
                v-if="isDirty"
                variant="filled"
                :text="__('Discard Changes')"
                @click="$refs.tree.cancel"
            />

            <site-selector
                v-if="sites.length > 1"
                :sites="sites"
                :model-value="site"
                @update:modelValue="siteSelected"
            />

            <Dropdown v-if="canEdit && hasCollections" placement="left-start" :disabled="!hasCollections">
                <template #trigger>
                    <Button
                        :text="__('Add')"
                        icon-append="chevron-down"
                    />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Add Nav Item')"
                        @click="linkPage()"
                        icon="add-list"
                    />
                    <DropdownItem
                        :text="__('Add Link to Entry')"
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
            @loaded="treeLoaded"
            @changed="treeChanged"
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
                        @click="linkPage()"
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
                <ui-icon v-if="isEntryBranch(branch)" v-tooltip="__('Entry link')" class="size-3.5! text-gray-500" name="link" tabindex="-1" />
                <ui-icon v-if="isLinkBranch(branch)" v-tooltip="__('External link')" class="size-3.5! text-gray-500" name="external-link" tabindex="-1" />
                <ui-icon v-if="isTextBranch(branch)" v-tooltip="__('Text')" class="size-3.5! text-gray-500" name="page" tabindex="-1" />
            </template>

            <template v-if="canEdit" #branch-options="{ branch, removeBranch, stat, depth }">
                <DropdownItem
                    v-if="isEntryBranch(stat)"
                    :text="__('Edit Entry')"
                    :href="branch.edit_url"
                    icon="edit"
                />
                <DropdownItem
                    :text="__('Edit Nav Item')"
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
            :query-scopes="entryQueryScopes"
            :max-items="maxPagesSelection"
            :can-select-across-sites="canSelectAcrossSites"
            :tree="collectionTree"
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
