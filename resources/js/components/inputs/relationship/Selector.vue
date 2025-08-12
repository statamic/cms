<template>
    <div class="dark:bg-dark-800 h-full bg-white rounded-s-xl">
        <div class="flex h-full min-h-0 flex-col">
            <Listing
                v-if="filters != null && view === 'list'"
                :url="selectionsUrl"
                :filters="filters"
                :max-selections="maxSelections"
                :sort-column="sortColumn"
                :sort-direction="sortDirection"
                v-model:selections="selections"
            >
                <template #initializing>
                    <div class="flex flex-1">
                        <div class="absolute inset-0 z-200 flex items-center justify-center text-center">
                            <Icon name="loading" />
                        </div>
                    </div>
                </template>

                <div class="flex flex-1 flex-col gap-4 overflow-scroll p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-1 items-center gap-3">
                            <Search />
                            <Filters v-if="filters && filters.length" />
                        </div>

                        <ui-toggle-group v-model="view" v-if="canUseTree">
                            <ui-toggle-item icon="hierarchy" value="tree" />
                            <ui-toggle-item icon="layout-list" value="list" />
                        </ui-toggle-group>
                    </div>

                    <Panel class="relative mb-0! overflow-x-auto overscroll-x-contain">
                        <Table />
                        <PanelFooter>
                            <Pagination />
                        </PanelFooter>
                    </Panel>
                </div>
            </Listing>

            <template v-if="view === 'tree'">
                <div class="flex justify-between p-4">
                    <Heading :text="__('Pages')" size="lg" />
                    <ui-toggle-group v-model="view" v-if="canUseTree">
                        <ui-toggle-item icon="hierarchy" value="tree" />
                        <ui-toggle-item icon="layout-list" value="list" />
                    </ui-toggle-group>
                </div>

                <div class="mx-4 flex-1 overflow-scroll">
                    <Panel>
                        <page-tree
                            ref="tree"
                            :pages-url="tree.url"
                            :show-slugs="tree.showSlugs"
                            :blueprints="tree.blueprints"
                            :expects-root="tree.expectsRoot"
                            :site="site"
                            :preferences-prefix="`selector-field.${name}`"
                            :editable="false"
                            @branch-clicked="$refs[`tree-branch-${$event.id}`].click()"
                        >
                            <template #branch-action="{ branch, index }">
                                <div>
                                    <Checkbox
                                        :ref="`tree-branch-${branch.id}`"
                                        class="mt-3 mx-3"
                                        :value="branch.id"
                                        :model-value="isSelected(branch.id)"
                                        :disabled="reachedSelectionLimit && !singleSelect && !isSelected(branch.id)"
                                        :label="getCheckboxLabel(branch)"
                                        :description="getCheckboxDescription(branch)"
                                        size="sm"
                                        solo
                                        @update:model-value="toggleSelection(branch.id)"
                                    />
                                </div>
                            </template>

                            <template #branch-icon="{ branch }">
                                <ui-icon name="external-link" v-if="isRedirectBranch(branch)" v-tooltip="__('Redirect')" />
                            </template>
                        </page-tree>
                    </Panel>
                </div>
            </template>

            <footer class="flex items-center justify-between border-t dark:border-dark-900 bg-gray-100 dark:bg-gray-800 p-4 rounded-es-xl">
                <ui-badge
                    v-text="
                        hasMaxSelections
                            ? __n(':count/:max selected', selections, { max: maxSelections })
                            : __n(':count item selected|:count items selected', selections)
                    "
                />

                <div class="flex items-center space-x-3">
                    <Button variant="ghost" @click="close">
                        {{ __('Cancel') }}
                    </Button>

                    <Button v-if="!hasMaxSelections || maxSelections > 1" variant="primary" @click="select">
                        {{ __('Select') }}
                    </Button>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
import clone from '@statamic/util/clone.js';
import {
    Button,
    ButtonGroup,
    Tooltip,
    Listing,
    ListingTable as Table,
    ListingSearch as Search,
    ListingPagination as Pagination,
    ListingFilters as Filters,
    Panel,
    PanelFooter,
    Heading,
    Checkbox,
    Icon,
} from '@statamic/ui';

export default {
    components: {
        PageTree: defineAsyncComponent(() => import('../../structures/PageTree.vue')),
        Button,
        ButtonGroup,
        Tooltip,
        Listing,
        Table,
        Search,
        Filters,
        Pagination,
        Panel,
        PanelFooter,
        Heading,
        Checkbox,
        Icon,
    },

    // todo, when opening and closing the stack, you cant save?

    props: {
        filtersUrl: String,
        selectionsUrl: String,
        initialSelections: Array,
        initialSortColumn: String,
        initialSortDirection: String,
        maxSelections: Number,
        site: String, // todo: this should be sent to the request.
        type: String, // todo: this controls the extra column that is commented out in the new table at the moment.
        name: String,
        initialColumns: {
            type: Array,
            default: () => [],
        },
        tree: Object,
    },

    data() {
        return {
            filters: null,
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            selections: clone(this.initialSelections),
            columns: this.initialColumns,
            view: 'list',
        };
    },

    computed: {
        hasMaxSelections() {
            return this.maxSelections === Infinity ? false : Boolean(this.maxSelections);
        },

        reachedSelectionLimit() {
            return this.selections.length === this.maxSelections;
        },

        singleSelect() {
            return this.maxSelections === 1;
        },

        canUseTree() {
            return !!this.tree;
        },

        initialView() {
            if (!this.canUseTree) return 'list';

            const fallback = this.canUseTree ? 'tree' : 'list';

            return localStorage.getItem(this.viewLocalStorageKey) || fallback;
        },

        viewLocalStorageKey() {
            return `statamic.selector.field.${this.name}`;
        },
    },

    mounted() {
        this.view = this.initialView;

        this.getFilters();
    },

    watch: {
        selections: {
            deep: true,
            handler: function () {
                if (this.maxSelections === 1 && this.selections.length === 1) {
                    this.select();
                }
            },
        },

        view(view) {
            localStorage.setItem(this.viewLocalStorageKey, view);
        },
    },

    methods: {
        getFilters() {
            if (!this.filtersUrl) return Promise.resolve();

            return this.$axios.get(this.filtersUrl).then((response) => {
                this.filters = response.data;
            });
        },

        select() {
            this.$emit('selected', this.selections);
            this.close();
        },

        close() {
            this.$emit('closed');
        },

        isRedirectBranch(branch) {
            return branch.redirect != null;
        },

        isSelected(id) {
            return this.selections.includes(id);
        },

        toggleSelection(id) {
            const i = this.selections.indexOf(id);

            if (i > -1) {
                this.selections.splice(i, 1);

                return;
            }

            if (this.singleSelect) {
                this.selections.pop();
            }

            if (!this.reachedSelectionLimit) {
                this.selections.push(id);
            }
        },

        getCheckboxLabel(row) {
            const rowTitle = this.getRowTitle(row);
            return this.isSelected(row.id)
                ? __('deselect_title', { title: rowTitle })
                : __('select_title', { title: rowTitle });
        },

        getCheckboxDescription(row) {
            const rowTitle = this.getRowTitle(row);
            const isDisabled = this.reachedSelectionLimit && !this.singleSelect && !this.isSelected(row.id);

            if (isDisabled) {
                return __('selection_limit_reached', { title: rowTitle });
            }

            return this.isSelected(row.id)
                ? __('item_selected_description', { title: rowTitle })
                : __('item_not_selected_description', { title: rowTitle });
        },

        getRowTitle(row) {
            // Try to get a meaningful title from common fields
            return row.title || row.name || row.label || row.id || __('item');
        },
    },
};
</script>
