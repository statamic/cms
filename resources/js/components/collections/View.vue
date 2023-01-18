<template>

    <div>

        <header class="mb-3">

            <breadcrumb :url="breadcrumbUrl" :title="__('Collections')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1" v-if="!!this.$scopedSlots.twirldown">
                    <slot name="twirldown" />
                </dropdown-list>

                <div class="btn-group mr-2" v-if="canUseStructureTree && !treeIsDirty">
                    <button class="btn flex items-center px-2" @click="view = 'tree'" :class="{'active': view === 'tree'}" v-tooltip="__('Tree')">
                        <svg-icon name="structures" class="h-4 w-4"/>
                    </button>
                    <button class="btn flex items-center px-2" @click="view = 'list'" :class="{'active': view === 'list'}" v-tooltip="__('List')">
                        <svg-icon name="assets-mode-table" class="h-4 w-4" />
                    </button>
                </div>

                <template v-if="view === 'tree'">

                    <a
                        class="text-2xs text-blue mr-2 underline"
                        v-if="treeIsDirty"
                        v-text="__('Discard changes')"
                        @click="cancelTreeProgress"
                    />

                    <site-selector
                        v-if="sites.length > 1"
                        class="mr-2"
                        :sites="sites"
                        :value="site"
                        @input="site = $event.handle"
                    />

                    <button
                        class="btn mr-2"
                        :class="{ 'disabled': !treeIsDirty, 'btn-danger': deletedEntries.length }"
                        :disabled="!treeIsDirty"
                        @click="saveTree"
                        v-text="__('Save Changes')"
                        v-tooltip="deletedEntries.length ? __n('An entry will be deleted|:count entries will be deleted', deletedEntries.length) : null" />

                </template>

                <template v-if="view === 'list' && reorderable">
                    <site-selector
                        v-if="sites.length > 1 && reordering && site"
                        class="mr-2"
                        :sites="sites"
                        :value="site"
                        @input="site = $event.handle"
                    />

                    <button class="btn mr-2"
                        v-if="!reordering"
                        @click="reordering = true"
                        v-text="__('Reorder')" />

                    <template v-if="reordering">
                        <button class="btn ml-1"
                            @click="reordering = false"
                            v-text="__('Cancel')" />

                        <button class="btn-primary ml-1"
                            @click="$refs.list.saveOrder"
                            v-text="__('Save Order')" />
                    </template>
                </template>

                <create-entry-button
                    v-if="!reordering && canCreate"
                    button-class="btn-primary"
                    :url="createUrl"
                    :blueprints="blueprints"
                    :text="createLabel" />
            </div>

        </header>

        <entry-list
            v-if="view === 'list'"
            ref="list"
            :collection="handle"
            :initial-sort-column="sortColumn"
            :initial-sort-direction="sortDirection"
            :initial-columns="columns"
            :filters="filters"
            :action-url="actionUrl"
            :reordering="reordering"
            :reorder-url="reorderUrl"
            :site="site"
            @reordered="reordering = false"
            @site-changed="site = $event"
        />

        <page-tree
            v-if="canUseStructureTree && view === 'tree'"
            ref="tree"
            :has-collection="true"
            :collections="[handle]"
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
                <svg-icon v-if="isRedirectBranch(branch)"
                    class="inline-block w-4 h-4 text-grey-50"
                    name="external-link"
                    v-tooltip="__('Redirect')" />
            </template>

            <template #branch-options="{ branch, removeBranch, orphanChildren, depth }">
                <template v-if="depth < structureMaxDepth">
                    <h6 class="px-1" v-text="__('Create Child Entry')" v-if="blueprints.length > 1" />
                    <li class="divider" v-if="blueprints.length > 1" />
                    <dropdown-item
                        v-for="blueprint in blueprints"
                        :key="blueprint.handle"
                        @click="createEntry(blueprint.handle, branch.id)"
                        v-text="blueprints.length > 1 ? blueprint.title : __('Create Child Entry')" />
                </template>
                <template v-if="branch.can_delete">
                    <li class="divider"></li>
                    <dropdown-item
                        :text="__('Delete')"
                        class="warning"
                        @click="deleteTreeBranch(branch, removeBranch, orphanChildren)" />
                </template>
            </template>
        </page-tree>

        <delete-entry-confirmation
            v-if="showEntryDeletionConfirmation"
            :children="numberOfChildrenToBeDeleted"
            @confirm="entryDeletionConfirmCallback"
            @cancel="showEntryDeletionConfirmation = false; entryBeingDeleted = null;"
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
import PageTree from '../structures/PageTree.vue';
import DeleteEntryConfirmation from './DeleteEntryConfirmation.vue';
import DeleteLocalizationConfirmation from './DeleteLocalizationConfirmation.vue';
import SiteSelector from '../SiteSelector.vue';

export default {

    components: {
        PageTree,
        DeleteEntryConfirmation,
        DeleteLocalizationConfirmation,
        SiteSelector
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrl: { type: String, required: true },
        createLabel: { type: String, required: true },
        blueprints: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        structured: { type: Boolean, default: false },
        sortColumn: { type: String, required: true },
        sortDirection: { type: String, required: true },
        columns: { type: Array, required: true },
        filters: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        reorderUrl: { type: String, required: true },
        initialSite: { type: String, required: true },
        sites: { type: Array },
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
        }
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
                entry.children.forEach(child => {
                    children++;
                    countChildren(child);
                });
            }
            countChildren(this.entryBeingDeleted);
            return children;
        }

    },

    watch: {

        view(view) {
            this.site = this.site || this.initialSite;

            this.$config.set('wrapperClass', view === 'tree' ? undefined : 'max-w-full');

            localStorage.setItem('statamic.collection-view.'+this.handle, view);
        }

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
            if (this.sites.length === 1 || this.deletedEntries.length === 0) {
                this.performTreeSaving();
                return;
            }

            this.showLocalizationDeleteBehaviorConfirmation = true;
            this.localizationDeleteBehaviorConfirmCallback = (behavior) => {
                this.deleteLocalizationBehavior = behavior;
                this.showLocalizationDeleteBehaviorConfirmation = false;
                this.$nextTick(() => this.performTreeSaving());
            }
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

            return localStorage.getItem('statamic.collection-view.'+this.handle) || fallback;
        },

        deleteTreeBranch(branch, removeFromUi, orphanChildren) {
            this.showEntryDeletionConfirmation = true;
            this.entryBeingDeleted = branch;
            this.entryDeletionConfirmCallback = (shouldDeleteChildren) => {
                this.deletedEntries.push(branch.id);
                shouldDeleteChildren ? this.markEntriesForDeletion(branch) : orphanChildren();
                removeFromUi();
                this.showEntryDeletionConfirmation = false;
                this.entryBeingDeleted = false;
            }
        },

        markEntriesForDeletion(branch) {
            const addDeletableChildren = (branch) => {
                branch.children.forEach(child => {
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

        editPage(page, vm, store, $event) {
            const url = page.edit_url;
            $event.metaKey ? window.open(url) : window.location = url;
        }

    }

}
</script>
