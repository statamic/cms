<template>

    <div>

        <header class="mb-3">

            <breadcrumb :url="breadcrumbUrl" :title="__('Collections')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <slot name="twirldown" />
                </dropdown-list>

                <div class="btn-group-flat mr-2" v-if="canUseStructureTree && !treeIsDirty">
                    <button @click="view = 'tree'" :class="{'active': view === 'tree'}">
                        <svg-icon name="structures" class="h-4 w-4"/>
                    </button>
                    <button @click="view = 'list'" :class="{'active': view === 'list'}">
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

                    <button
                        class="btn mx-2"
                        :class="{ 'disabled': !treeIsDirty, 'btn-danger': deletedEntries.length }"
                        :disabled="!treeIsDirty"
                        @click="saveTree"
                        v-text="__('Save Changes')"
                        v-tooltip="deletedEntries.length ? __n('An entry will be deleted|:count entries will be deleted', deletedEntries.length) : null" />

                </template>

                <create-entry-button
                    button-class="btn-primary"
                    :url="createUrl"
                    :blueprints="blueprints" />
            </div>

        </header>

        <entry-list
            v-show="view === 'list'"
            :collection="handle"
            :initial-sort-column="sortColumn"
            :initial-sort-direction="sortDirection"
            :filters="filters"
            :action-url="actionUrl"
            :reorderable="reorderable"
            :reorder-url="reorderUrl"
            :site="site"
        />

        <page-tree
            v-if="canUseStructureTree"
            v-show="view === 'tree'"
            ref="tree"
            :has-collection="true"
            :collections="[handle]"
            :create-url="createUrl"
            :pages-url="structurePagesUrl"
            :submit-url="structureSubmitUrl"
            :submit-parameters="{ deletedEntries }"
            :max-depth="structureMaxDepth"
            :expects-root="structureExpectsRoot"
            :site="site"
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

            <template #branch-options="{ branch, removeBranch, orphanChildren }">
                <dropdown-item
                    v-for="blueprint in blueprints"
                    :key="blueprint.handle"
                    @click="createEntry(blueprint.handle, branch.id)"
                    v-text="__('New :thing', { thing: blueprint.title })" />
                <dropdown-item
                    :text="__('Delete')"
                    class="warning"
                    @click="deleteTreeBranch(branch, removeBranch, orphanChildren)" />
            </template>
        </page-tree>

        <delete-entry-confirmation
            v-if="showEntryDeletionConfirmation"
            @confirm="entryDeletionConfirmCallback"
            @cancel="showEntryDeletionConfirmation = false"
        />

    </div>

</template>

<script>
import PageTree from '../structures/PageTree.vue';
import DeleteEntryConfirmation from './DeleteEntryConfirmation.vue';

export default {

    components: {
        PageTree,
        DeleteEntryConfirmation
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrl: { type: String, required: true },
        blueprints: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        structured: { type: Boolean, default: false },
        sortColumn: { type: String, required: true },
        sortDirection: { type: String, required: true },
        filters: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        reorderUrl: { type: String, required: true },
        blueprints: { type: Array, required: true },
        site: { type: String, required: true },
        structurePagesUrl: { type: String },
        structureSubmitUrl: { type: String },
        structureMaxDepth: { type: Number, default: Infinity },
        structureExpectsRoot: { type: Boolean },
    },

    data() {
        return {
            mounted: false,
            view: null,
            deletedEntries: [],
            showEntryDeletionConfirmation: false,
            entryDeletionConfirmCallback: null,
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

    },

    watch: {

        view(view) {
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
            this.$refs.tree.save();
            this.deletedEntries = [];
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
            this.entryDeletionConfirmCallback = (shouldDeleteChildren) => {
                this.deletedEntries.push(branch.id);
                if (shouldDeleteChildren) {
                    const addDeletableChildren = (branch) => {
                        branch.children.forEach(child => {
                            this.deletedEntries.push(child.id);
                            addDeletableChildren(child);
                        });
                    };
                    addDeletableChildren(branch);
                } else {
                    orphanChildren();
                }
                removeFromUi();
                this.showEntryDeletionConfirmation = false;
            }
        },

        isRedirectBranch(branch) {
            return branch.redirect != null;
        },

        createEntry(blueprint, parent) {
            let url = `${this.createUrl}?blueprint=${blueprint}`;
            if (parent) url += '&parent=' + parent;
            window.location = url;
        },

    }

}
</script>
