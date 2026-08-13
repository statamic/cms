<script>
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, DropdownLabel, DropdownSeparator, Listing, Button, ToggleGroup, ToggleItem } from '@ui';
import { Link, router } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

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
            deletedTerms: [],
            termBeingDeleted: null,
        };
    },

    computed: {
        canUseStructureTree() {
            return this.structured;
        },

        treeIsDirty() {
            return this.$dirty.has('page-tree');
        },

        maxDepth() {
            return this.structureMaxDepth || Infinity;
        },
    },

    watch: {
        view(view) {
            this.$preferences.set(`taxonomies.${this.taxonomy}.view`, view);
        },
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
            this.termBeingDeleted = { branch, removeFromUi };
        },

        confirmDeleteTreeBranch() {
            const { branch, removeFromUi } = this.termBeingDeleted;
            this.deletedTerms.push(branch.id);
            removeFromUi(false); // Children are kept and promoted into the deleted term's position.
            this.termBeingDeleted = null;
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

            <ToggleGroup v-model="view" v-if="canUseStructureTree">
                <ToggleItem icon="navigation" value="tree" />
                <ToggleItem icon="layout-list" value="list" />
            </ToggleGroup>

            <create-term-button
                v-if="canCreate"
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
            push-query
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
                <DropdownItem v-if="term.has_template" :text="__('Visit URL')" :href="term.permalink" target="_blank" icon="eye" />
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

        <confirmation-modal
            :open="termBeingDeleted !== null"
            :title="__('Delete Term')"
            :body-text="termBeingDeleted?.branch.children.length
                ? __('messages.term_delete_with_children_confirmation')
                : __('Are you sure you want to delete this term?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="confirmDeleteTreeBranch"
            @cancel="termBeingDeleted = null"
        />
    </div>
</template>
