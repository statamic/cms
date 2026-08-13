<script>
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, DropdownLabel, DropdownSeparator, Listing, Button, ToggleGroup, ToggleItem } from '@ui';
import { Link, router } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import DeleteTermConfirmation from '@/components/taxonomies/DeleteTermConfirmation.vue';

export default {
    components: {
        Link,
        Head,
        Header,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        DropdownSeparator,
        Listing,
        Button,
        ToggleGroup,
        ToggleItem,
        DeleteTermConfirmation,
        PageTree: defineAsyncComponent(() => import('@/components/structures/PageTree.vue')),
    },

    props: [
        'taxonomy',
        'taxonomyTitle',
        'blueprints',
        'site',
        'columns',
        'filters',
        'canCreate',
        'createUrl',
        'reorderUrl',
        'actionUrl',
        'sortColumn',
        'sortDirection',
        'taxonomyEditUrl',
        'taxonomyBlueprintsUrl',
        'canEdit',
        'canDelete',
        'canConfigureFields',
        'deleteUrl',
        'createLabel',
        'structured',
        'structurePagesUrl',
        'structureSubmitUrl',
        'structureMaxDepth',
    ],

    data() {
        return {
            preferencesPrefix: `taxonomies.${this.taxonomy}`,
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
            view: null,
            reordering: false,
            items: null,
            page: null,
            perPage: null,
            saveKeyBinding: null,
            deletedTerms: [],
            showTermDeletionConfirmation: false,
            termBeingDeleted: null,
            termDeletionConfirmCallback: null,
        };
    },

    computed: {
        canUseStructureTree() {
            return this.structured && this.structureMaxDepth !== 1;
        },

        reorderable() {
            return this.structured && this.structureMaxDepth === 1;
        },

        treeIsDirty() {
            return this.$dirty.has('page-tree');
        },

        maxDepth() {
            return this.structureMaxDepth || Infinity;
        },

        numberOfChildrenToBeDeleted() {
            let children = 0;
            const countChildren = (term) => {
                term.children.forEach((child) => {
                    children++;
                    countChildren(child);
                });
            };
            countChildren(this.termBeingDeleted);
            return children;
        },
    },

    watch: {
        view(view) {
            this.$preferences.set(`taxonomies.${this.taxonomy}.view`, view);
        },
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
            if (this.reordering) {
                e.preventDefault();
                this.saveOrder();
            }
        });
    },

    beforeUnmount() {
        this.saveKeyBinding?.destroy();
    },

    mounted() {
        this.view = this.initialView();

        this.addToCommandPalette();
    },

    methods: {
        initialView() {
            if (!this.canUseStructureTree) return 'list';

            const savedView = this.$preferences.get(`taxonomies.${this.taxonomy}.view`);

            return savedView === 'list' ? 'list' : 'tree';
        },

        deleteTaxonomy() {
            this.$refs.deleter.confirm();
        },

        cancelTreeProgress() {
            this.$refs.tree.cancel();
            this.deletedTerms = [];
        },

        saveTree() {
            this.$refs.tree
                .save()
                .then(() => (this.deletedTerms = []))
                .catch(() => {});
        },

        markTreeDirty() {
            this.$dirty.add('page-tree');
        },

        markTreeClean() {
            this.$dirty.remove('page-tree');
        },

        deleteTreeBranch(branch, removeFromUi) {
            this.showTermDeletionConfirmation = true;
            this.termBeingDeleted = branch;
            this.termDeletionConfirmCallback = (shouldDeleteChildren) => {
                this.deletedTerms.push(branch.id);
                if (shouldDeleteChildren) this.markTermsForDeletion(branch);
                removeFromUi(shouldDeleteChildren);
                this.showTermDeletionConfirmation = false;
                this.termBeingDeleted = null;
            };
        },

        markTermsForDeletion(branch) {
            const addDeletableChildren = (branch) => {
                branch.children.forEach((child) => {
                    this.deletedTerms.push(child.id);
                    addDeletableChildren(child);
                });
            };

            addDeletableChildren(branch);
        },

        createTerm(blueprint, parent) {
            let url = `${this.createUrl}?blueprint=${blueprint}`;
            if (parent) url += '&parent=' + parent;
            router.get(url);
        },

        editTerm(term, $event) {
            const url = term.edit_url;
            $event.metaKey ? window.open(url) : router.get(url);
        },

        requestComplete({ items, parameters }) {
            this.items = items;
            this.page = parameters.page;
            this.perPage = parameters.perPage;
        },

        reordered(items) {
            this.items = items;
        },

        saveOrder() {
            this.$axios
                .post(this.reorderUrl, {
                    ids: this.items.map((item) => item.id),
                    page: this.page,
                    perPage: this.perPage,
                    site: this.site,
                })
                .then(() => {
                    this.reordering = false;
                    this.$toast.success(__('Terms successfully reordered'));
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
                });
        },

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                when: () => this.canCreate,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Create Term'),
                icon: 'taxonomies',
                url: this.createUrl,
                prioritize: true,
            });

            Statamic.$commandPalette.add({
                when: () => this.canEdit,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Configure Taxonomy'),
                icon: 'cog',
                url: this.taxonomyEditUrl,
            });

            Statamic.$commandPalette.add({
                when: () => this.canConfigureFields,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprints'),
                icon: 'blueprint-edit',
                url: this.taxonomyBlueprintsUrl,
            });

            Statamic.$commandPalette.add({
                when: () => this.canDelete,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Delete Taxonomy'),
                icon: 'trash',
                action: () => this.deleteTaxonomy(),
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Switch to List Layout'),
                icon: 'layout-list',
                when: () => this.canUseStructureTree && this.view !== 'list',
                action: () => (this.view = 'list'),
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Switch to Tree Layout'),
                icon: 'navigation',
                when: () => this.canUseStructureTree && this.view !== 'tree',
                action: () => (this.view = 'tree'),
            });
        },
    },
};
</script>

<template>
    <div>
        <Head :title="[__(taxonomyTitle), __('Taxonomies')]" />

        <Header :title="__(taxonomyTitle)">
            <Dropdown>
                <DropdownMenu>
                    <DropdownItem v-if="canEdit" :text="__('Configure Taxonomy')" icon="cog" :href="taxonomyEditUrl" />
                    <DropdownItem v-if="canConfigureFields" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="taxonomyBlueprintsUrl" />
                    <DropdownItem v-if="canDelete" :text="__('Delete Taxonomy')" icon="trash" variant="destructive" @click="deleteTaxonomy()" />
                </DropdownMenu>
            </Dropdown>

            <template v-if="view === 'tree'">
                <Button
                    v-if="treeIsDirty"
                    variant="filled"
                    :text="__('Discard Changes')"
                    @click="cancelTreeProgress"
                />

                <Button
                    v-if="treeIsDirty"
                    :text="__('Save Changes')"
                    :variant="deletedTerms.length ? 'danger' : 'default'"
                    @click="saveTree"
                    v-tooltip="deletedTerms.length ? __n('A term will be deleted|:count terms will be deleted', deletedTerms.length) : null"
                />
            </template>

            <template v-if="view === 'list' && reorderable">
                <Button
                    v-if="!reordering"
                    @click="reordering = true"
                    :text="__('Reorder')"
                />

                <template v-if="reordering">
                    <Button @click="reordering = false" :text="__('Cancel')" />
                    <Button @click="saveOrder" :text="__('Save Order')" variant="primary" />
                </template>
            </template>

            <ToggleGroup v-model="view" v-if="canUseStructureTree">
                <ToggleItem icon="navigation" value="tree" />
                <ToggleItem icon="layout-list" value="list" />
            </ToggleGroup>

            <create-term-button
                v-if="!reordering && canCreate"
                :url="createUrl"
                :text="createLabel"
                :blueprints="blueprints"
            />
        </Header>

        <resource-deleter
            v-if="canDelete"
            ref="deleter"
            :resource-title="taxonomyTitle"
            :route="deleteUrl"
            :redirect="cp_url('taxonomies')"
        />

        <Listing
            v-if="view === 'list'"
            ref="listing"
            :url="requestUrl"
            :columns="columns"
            :action-url="actionUrl"
            :action-context="{ taxonomy }"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            :preferences-prefix="preferencesPrefix"
            :filters="filters"
            :reorderable="reordering"
            push-query
            @request-completed="requestComplete"
            @reordered="reordered"
        >
            <template #cell-title="{ row: term }">
                <div class="flex items-center gap-2">
                    <Link :href="term.edit_url">{{ term.title }}</Link>
                    <span v-if="term.parent_path" class="text-2xs text-gray-500 dark:text-gray-400" v-text="term.parent_path" />
                </div>
            </template>
            <template #cell-slug="{ row: term }">
                <span class="text-2xs font-mono">{{ term.slug }}</span>
            </template>
            <template #prepended-row-actions="{ row: term }">
                <DropdownItem v-if="term.has_template && term.permalink" :text="__('Visit URL')" :href="term.permalink" target="_blank" icon="eye" />
                <DropdownItem :text="__('Edit')" :href="term.edit_url" icon="edit" />
            </template>
        </Listing>

        <page-tree
            v-if="canUseStructureTree && view === 'tree'"
            ref="tree"
            :blueprints="blueprints"
            :create-url="createUrl"
            :pages-url="structurePagesUrl"
            :submit-url="structureSubmitUrl"
            :submit-parameters="{ deletedTerms }"
            :max-depth="maxDepth"
            :expects-root="false"
            :site="site"
            :preferences-prefix="preferencesPrefix"
            @edit-page="editTerm"
            @changed="markTreeDirty"
            @saved="markTreeClean"
        >
            <template #branch-options="{ branch, removeBranch, depth }">
                <template v-if="canCreate && depth < maxDepth">
                    <DropdownLabel :text="__('Create Child Term')" v-if="blueprints.length > 1" />
                    <DropdownItem
                        v-for="blueprint in blueprints"
                        @click="createTerm(blueprint.handle, branch.id)"
                        :icon="blueprint.icon || 'taxonomies'"
                        :key="blueprint.handle"
                        :text="blueprints.length > 1 ? __(blueprint.title) : __('Create Child Term')"
                    />
                </template>
                <DropdownSeparator v-if="canCreate && depth < maxDepth && branch.can_delete" />
                <DropdownItem
                    v-if="branch.can_delete"
                    :text="__('Delete')"
                    icon="trash"
                    variant="destructive"
                    @click="deleteTreeBranch(branch, removeBranch)"
                />
            </template>
        </page-tree>

        <delete-term-confirmation
            v-if="showTermDeletionConfirmation"
            :children="numberOfChildrenToBeDeleted"
            @confirm="termDeletionConfirmCallback"
            @cancel="
                showTermDeletionConfirmation = false;
                termBeingDeleted = null;
            "
        />
    </div>
</template>
