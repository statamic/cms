<template>
    <div>
        <Header :title="__(title)" :icon="icon">
            <ItemActions
                :url="actionUrl"
                :actions="actions"
                :item="handle"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions }"
            >
                <Dropdown placement="left-start">
                    <DropdownMenu>
                        <DropdownItem v-if="canEdit" :text="__('Configure Collection')" icon="cog" :href="editUrl" />
                        <DropdownItem v-if="canEditBlueprints" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="blueprintsUrl" />
                        <DropdownItem v-if="canEdit" :text="__('Scaffold Views')" icon="scaffold" :href="scaffoldUrl" />
                        <DropdownSeparator v-if="canEdit || canEditBlueprints || actions.length" />
                        <DropdownItem
                            v-for="action in actions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>

            <template v-if="view === 'tree'">
                <ui-button
                    v-if="treeIsDirty"
                    variant="filled"
                    :text="__('Discard changes')"
                    @click="cancelTreeProgress"
                />

                <site-selector
                    v-if="sites.length > 1"
                    :sites="sites"
                    :value="site"
                    @input="site = $event.handle"
                />

                <Button
                    v-if="treeIsDirty"
                    :disabled="!treeIsDirty"
                    :text="__('Save Changes')"
                    :variant="deletedEntries.length ? 'danger' : 'default'"
                    @click="saveTree"
                    v-tooltip="deletedEntries.length ? __n('An entry will be deleted|:count entries will be deleted', deletedEntries.length) : null"
                />
            </template>

            <ui-button
                v-if="canCreateCollections"
                :href="createUrl"
                :text="__('Create Collection')"
                variant="primary"
            />

            <ui-toggle-group v-model="view" v-if="canUseStructureTree">
                <ui-toggle-item icon="navigation" value="tree" />
                <ui-toggle-item icon="layout-list" value="list" />
            </ui-toggle-group>

            <template v-if="view === 'list' && reorderable">
                <site-selector
                    v-if="sites.length > 1 && reordering && site"
                    :sites="sites"
                    :value="site"
                    @input="site = $event"
                />

                <Button
                    v-if="!reordering"
                    @click="reordering = true"
                    :text="__('Reorder')"
                />

                <template v-if="reordering">
                    <Button @click="reordering = false" :text="__('Cancel')" />
                    <Button @click="$refs.list.saveOrder" :text="__('Save Order')" variant="primary" />
                </template>
            </template>

            <create-entry-button
                v-if="!reordering && canCreate"
                :url="createUrl"
                :blueprints="blueprints"
                :text="createLabel"
            />
        </Header>

        <entry-list
            v-if="view === 'list'"
            ref="list"
            :collection="handle"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            :columns="columns"
            :filters="filters"
            :action-url="entriesActionUrl"
            :reordering="reordering"
            :reorder-url="reorderUrl"
            :site="site"
            @reordered="reordering = false"
            @site-changed="site = $event"
        />

        <page-tree
            v-if="canUseStructureTree && view === 'tree'"
            ref="tree"
            :collections="[handle]"
            :blueprints="blueprints"
            :create-url="createUrl"
            :pages-url="structurePagesUrl"
            :submit-url="structureSubmitUrl"
            :submit-parameters="{ deletedEntries, deleteLocalizationBehavior }"
            :max-depth="structureMaxDepth"
            :expects-root="structureExpectsRoot"
            :show-slugs="structureShowSlugs"
            :site="site"
            :preferences-prefix="preferencesPrefix"
            @edit-page="editPage"
            @changed="markTreeDirty"
            @saved="markTreeClean"
            @canceled="markTreeClean"
        >
            <template #branch-icon="{ branch }">
                <ui-icon
                    v-if="isRedirectBranch(branch)"
                    name="external-link"
                    v-tooltip="__('Redirect')"
                />
            </template>

            <template #branch-options="{ branch, removeBranch, depth }">
                <template v-if="depth < structureMaxDepth">
                    <h6 class="px-2" v-text="__('Create Child Entry')" v-if="blueprints.length > 1" />
                    <DropdownSeparator v-if="blueprints.length > 1" />
                    <DropdownItem
                        v-for="blueprint in blueprints"
                        :key="blueprint.handle"
                        @click="createEntry(blueprint.handle, branch.id)"
                        :text="blueprints.length > 1 ? __(blueprint.title) : __('Create Child Entry')"
                    />
                </template>
                <template v-if="branch.can_delete">
                    <DropdownSeparator />
                    <DropdownItem
                        :text="__('Delete')"
                        variant="destructive"
                        @click="deleteTreeBranch(branch, removeBranch)"
                    />
                </template>
            </template>
        </page-tree>

        <delete-entry-confirmation
            v-if="showEntryDeletionConfirmation"
            :children="numberOfChildrenToBeDeleted"
            @confirm="entryDeletionConfirmCallback"
            @cancel="
                showEntryDeletionConfirmation = false;
                entryBeingDeleted = null;
            "
        />

        <delete-localization-confirmation
            v-if="showLocalizationDeleteBehaviorConfirmation"
            :entries="deletedEntries.length"
            @confirm="localizationDeleteBehaviorConfirmCallback"
            @cancel="showLocalizationDeleteBehaviorConfirmation = false"
        />
    </div>
</template>

<script>
import DeleteEntryConfirmation from './DeleteEntryConfirmation.vue';
import DeleteLocalizationConfirmation from './DeleteLocalizationConfirmation.vue';
import SiteSelector from '../SiteSelector.vue';
import HasActions from '../publish/HasActions';
import { defineAsyncComponent } from 'vue';
import { Dropdown, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator, Header, Button, ToggleGroup, ToggleItem } from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    mixins: [HasActions],

    components: {
        DropdownSeparator,
        DropdownItem,
        DropdownLabel,
        DropdownMenu,
        ItemActions,
        Dropdown,
        Header,
        Button,
        ToggleGroup,
        ToggleItem,
        PageTree: defineAsyncComponent(() => import('../structures/PageTree.vue')),
        DeleteEntryConfirmation,
        DeleteLocalizationConfirmation,
        SiteSelector,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        icon: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrls: { type: Object, required: true },
        createLabel: { type: String, required: true },
        blueprints: { type: Array, required: true },
        structured: { type: Boolean, default: false },
        sortColumn: { type: String, required: true },
        sortDirection: { type: String, required: true },
        columns: { type: Array, required: true },
        filters: { type: Array, required: true },
        actions: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        entriesActionUrl: { type: String, required: true },
        reorderUrl: { type: String, required: true },
        editUrl: { type: String, required: true },
        blueprintsUrl: { type: String, required: true },
        scaffoldUrl: { type: String, required: true },
        canEdit: { type: Boolean, required: true },
        canEditBlueprints: { type: Boolean, required: true },
        initialSite: { type: String, required: true },
        sites: { type: Array },
        totalSitesCount: { type: Number },
        canChangeLocalizationDeleteBehavior: { type: Boolean },
        structurePagesUrl: { type: String },
        structureSubmitUrl: { type: String },
        structureMaxDepth: { type: Number, default: Infinity },
        structureExpectsRoot: { type: Boolean },
        structureShowSlugs: { type: Boolean },
    },

    data() {
        return {
            mounted: false,
            view: null,
            deletedEntries: [],
            showEntryDeletionConfirmation: false,
            entryBeingDeleted: null,
            entryDeletionConfirmCallback: null,
            deleteLocalizationBehavior: null,
            showLocalizationDeleteBehaviorConfirmation: false,
            localizationDeleteBehaviorConfirmCallback: null,
            site: this.initialSite,
            reordering: false,
            preferencesPrefix: `collections.${this.handle}`,
        };
    },

    computed: {
        treeIsDirty() {
            return this.$dirty.has('page-tree');
        },

        canUseStructureTree() {
            return this.structured && this.structureMaxDepth !== 1;
        },

        reorderable() {
            return this.structured && this.structureMaxDepth === 1;
        },

        numberOfChildrenToBeDeleted() {
            let children = 0;
            const countChildren = (entry) => {
                entry.children.forEach((child) => {
                    children++;
                    countChildren(child);
                });
            };
            countChildren(this.entryBeingDeleted);
            return children;
        },

        createUrl() {
            return this.createUrls[this.site || this.initialSite];
        },
    },

    watch: {
        view(view) {
            this.site = this.site || this.initialSite;

            localStorage.setItem('statamic.collection-view.' + this.handle, view);
        },
    },

    mounted() {
        this.view = this.initialView();
        this.mounted = true;
    },

    methods: {
        cancelTreeProgress() {
            this.$refs.tree.cancel();
            this.deletedEntries = [];
        },

        saveTree() {
            if (this.deletedEntries.length === 0) {
                this.performTreeSaving();
                return;
            }

            // When the user doesn't have permission to access the sites the entry is localized in,
            // we should use the "copy" behavior to detach the entry from the site.
            if (!this.canChangeLocalizationDeleteBehavior) {
                this.deleteLocalizationBehavior = 'copy';
                this.$nextTick(() => this.performTreeSaving());
                return;
            }

            this.showLocalizationDeleteBehaviorConfirmation = true;
            this.localizationDeleteBehaviorConfirmCallback = (behavior) => {
                this.deleteLocalizationBehavior = behavior;
                this.showLocalizationDeleteBehaviorConfirmation = false;
                this.$nextTick(() => this.performTreeSaving());
            };
        },

        performTreeSaving() {
            this.$refs.tree
                .save()
                .then(() => (this.deletedEntries = []))
                .catch(() => {});
        },

        markTreeDirty() {
            this.$dirty.add('page-tree');
        },

        markTreeClean() {
            this.$dirty.remove('page-tree');
        },

        initialView() {
            if (!this.canUseStructureTree) return 'list';

            const fallback = this.canUseStructureTree ? 'tree' : 'list';

            return localStorage.getItem('statamic.collection-view.' + this.handle) || fallback;
        },

        deleteTreeBranch(branch, removeFromUi) {
            this.showEntryDeletionConfirmation = true;
            this.entryBeingDeleted = branch;
            this.entryDeletionConfirmCallback = (shouldDeleteChildren) => {
                this.deletedEntries.push(branch.id);
                if (shouldDeleteChildren) this.markEntriesForDeletion(branch);
                removeFromUi(shouldDeleteChildren);
                this.showEntryDeletionConfirmation = false;
                this.entryBeingDeleted = false;
            };
        },

        markEntriesForDeletion(branch) {
            const addDeletableChildren = (branch) => {
                branch.children.forEach((child) => {
                    this.deletedEntries.push(child.id);
                    addDeletableChildren(child);
                });
            };

            addDeletableChildren(branch);
        },

        isRedirectBranch(branch) {
            return branch.redirect != null;
        },

        createEntry(blueprint, parent) {
            let url = `${this.createUrl}?blueprint=${blueprint}`;
            if (parent) url += '&parent=' + parent;
            window.location = url;
        },

        editPage(page, $event) {
            const url = page.edit_url;
            $event.metaKey ? window.open(url) : (window.location = url);
        },

        afterActionSuccessfullyCompleted(response) {
            if (!response.redirect) window.location.reload();
        },
    },
};
</script>
