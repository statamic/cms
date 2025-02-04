<template>

    <div class="h-full bg-white dark:bg-dark-800">

        <div v-if="initializing" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing && view === 'list'"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            :selections="selections"
            :max-selections="maxSelections"
            @selections-updated="selectionsUpdated"
            v-slot="{}"
        >
            <div class="flex flex-col h-full">
                <div class="bg-white dark:bg-dark-800 z-1">
                    <div class="py-2 px-4 flex items-center justify-between">
                        <data-list-search class="h-8 min-w-[240px] w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />
                        <div class="btn-group rtl:mr-4 ltr:ml-4" v-if="canUseTree">
                            <button class="btn flex items-center px-4" @click="view = 'tree'" :class="{'active': view === 'tree'}" v-tooltip="__('Tree')">
                                <svg-icon name="light/structures" class="h-4 w-4"/>
                            </button>
                            <button class="btn flex items-center px-4" @click="view = 'list'" :class="{'active': view === 'list'}" v-tooltip="__('List')">
                                <svg-icon name="assets-mode-table" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div>
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            @changed="filterChanged($event, false)"
                        />
                    </div>
                </div>

                <div class="flex-1 flex flex-col min-h-0">
                    <div class="flex flex-col h-full justify-start">
                        <div class="flex-1 overflow-scroll">
                            <data-list-table
                                :loading="loading"
                                :allow-bulk-actions="true"
                                :toggle-selection-on-row-click="true"
                                :type="type"
                                @sorted="sorted"
                                class="cursor-pointer"
                            >
                                <template #cell-title="{ row: entry }">
                                    <div class="flex items-center">
                                        <div class="little-dot rtl:ml-2 ltr:mr-2" v-tooltip="getStatusLabel(entry)" :class="getStatusClass(entry)" v-if="entry.status && ! columnShowing('status')" />
                                        {{ entry.title }}
                                    </div>
                                </template>
                                <template #cell-status="{ row: entry }">
                                    <div class="status-index-field select-none" v-tooltip="getStatusTooltip(entry)" :class="`status-${entry.status}`" v-text="getStatusLabel(entry)" />
                                </template>
                                <template #cell-url="{ row: entry }">
                                    <span class="text-2xs">{{ entry.url }}</span>
                                </template>
                            </data-list-table>
                        </div>

                        <data-list-pagination
                            v-if="meta.last_page > 1"
                            class="border-t shadow-lg"
                            :resource-meta="meta"
                            :inline="true"
                            :scroll-to-top="false"
                            @page-selected="setPage" />

                        <div class="p-4 border-t dark:border-dark-200 flex items-center justify-between bg-gray-200 dark:bg-dark-500">
                            <div class="text-sm text-gray-700 dark:text-dark-150"
                                v-text="hasMaxSelections
                                    ? __n(':count/:max selected', selections, { max: maxSelections })
                                    : __n(':count item selected|:count items selected', selections)" />

                            <div>
                                <button
                                    type="button"
                                    class="btn"
                                    @click="close">
                                    {{ __('Cancel') }}
                                </button>

                                <button
                                    v-if="! hasMaxSelections || maxSelections > 1"
                                    type="button"
                                    class="btn-primary rtl:mr-2 ltr:ml-2"
                                    @click="select">
                                    {{ __('Select') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </data-list>

        <template v-if="!initializing && canUseTree && view === 'tree'">
            <div class="flex flex-col h-full">
                <div class="bg-white dark:bg-dark-550 shadow px-4 py-2 z-1 h-13 flex items-center justify-end">
                    <h1 class="flex-1 flex items-center text-xl">{{ tree.title }}</h1>
                    <div class="btn-group rtl:mr-4 ltr:ml-4">
                        <button class="btn flex items-center px-4" @click="view = 'tree'" :class="{'active': view === 'tree'}" v-tooltip="__('Tree')">
                            <svg-icon name="light/structures" class="h-4 w-4"/>
                        </button>
                        <button class="btn flex items-center px-4" @click="view = 'list'" :class="{'active': view === 'list'}" v-tooltip="__('List')">
                            <svg-icon name="assets-mode-table" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 flex flex-col min-h-0">
                    <div class="flex flex-col h-full justify-start">
                        <div class="flex-1 overflow-scroll bg-gray-200 dark:bg-dark-800 p-4">
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
                                        <input
                                            :ref="`tree-branch-${branch.id}`"
                                            type="checkbox"
                                            class="mt-3 rtl:mr-3 ltr:ml-3"
                                            :value="branch.id"
                                            :checked="isSelected(branch.id)"
                                            :disabled="reachedSelectionLimit && !singleSelect && !isSelected(branch.id)"
                                            :id="`checkbox-${branch.id}`"
                                            @click="checkboxClicked(branch, index, $event)"
                                        />
                                    </div>
                                </template>

                                <template #branch-icon="{ branch }">
                                    <svg-icon v-if="isRedirectBranch(branch)"
                                        class="inline-block w-4 h-4 text-gray-500 dark:text-dark-175"
                                        name="light/external-link"
                                        v-tooltip="__('Redirect')" />
                                </template>
                            </page-tree>
                        </div>

                        <div class="p-4 border-t dark:border-dark-200 flex items-center justify-between bg-gray-200 dark:bg-dark-500">
                            <div class="text-sm text-gray-700"
                                v-text="hasMaxSelections
                                    ? __n(':count/:max selected', selections, { max: maxSelections })
                                    : __n(':count item selected|:count items selected', selections)" />

                            <div>
                                <button
                                    type="button"
                                    class="btn"
                                    @click="close">
                                    {{ __('Cancel') }}
                                </button>

                                <button
                                    v-if="! hasMaxSelections || maxSelections > 1"
                                    type="button"
                                    class="btn-primary rtl:mr-2 ltr:ml-2"
                                    @click="select">
                                    {{ __('Select') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

</template>

<script>
import HasFilters from '../../data-list/HasFilters';
import PageTree from '../../structures/PageTree.vue';

export default {

    mixins: [
        HasFilters,
    ],

    components: {
        PageTree,
    },

    props: {
        filtersUrl: String,
        selectionsUrl: String,
        initialSelections: Array,
        initialSortColumn: String,
        initialSortDirection: String,
        maxSelections: Number,
        site: String,
        search: Boolean,
        type: String,
        name: String,
        exclusions: {
            type: Array,
            default: () => []
        },
        initialColumns: {
            type: Array,
            default: () => []
        },
        tree: Object,
    },

    data() {
        return {
            source: null,
            initializing: true,
            loading: true,
            items: [],
            meta: {},
            filters: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            page: 1,
            selections: _.clone(this.initialSelections),
            columns: this.initialColumns,
            visibleColumns: this.initialColumns.filter(column => column.visible),
            view: 'list',
            lastItemClicked: null
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                site: this.site,
                exclusions: this.exclusions,
                filters: utf8btoa(JSON.stringify(this.activeFilters)),
                columns: this.visibleColumns.map(column => column.field).join(','),
            }
        },

        hasMaxSelections() {
            return (this.maxSelections === Infinity) ? false : Boolean(this.maxSelections);
        },

        reachedSelectionLimit() {
            return this.selections.length === this.maxSelections;
        },

        singleSelect() {
            return this.maxSelections === 1;
        },

        canUseTree() {
            return !! this.tree;
        },

        initialView() {
            if (!this.canUseTree) return 'list';

            const fallback = this.canUseTree ? 'tree' : 'list';

            return localStorage.getItem(this.viewLocalStorageKey) || fallback;
        },

        viewLocalStorageKey() {
            return `statamic.selector.field.${this.name}`;
        }

    },

    mounted() {
        this.view = this.initialView;

        this.getFilters().then(() => {
            this.autoApplyFilters(this.filters);
            this.initialRequest();
        });
    },

    watch: {

        parameters: {
            deep: true,
            handler(after, before) {
                if (this.initializing) return;
                if (JSON.stringify(before) === JSON.stringify(after)) return;
                this.request();
            }
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading('relationship-selector-listing', loading);
            }
        },

        searchQuery(query) {
            this.sortColumn = null;
            this.sortDirection = null;
            this.page = 1;

            this.request();
        },

        selections() {
            if (this.maxSelections === 1 && this.selections.length === 1) {
                this.select();
            }
        },

        view(view) {
            localStorage.setItem(this.viewLocalStorageKey, view);
        },

    },

    methods: {

        getFilters() {
            if (!this.filtersUrl) return Promise.resolve();

            return this.$axios.get(this.filtersUrl).then(response => {
                this.filters = response.data;
            });
        },

        initialRequest() {
            return this.request().then(() => {
                if (this.search && this.view === 'list') this.$refs.search.focus();
            });
        },

        request() {
            this.loading = true;

            if (this.source) this.source.cancel();
            this.source = this.$axios.CancelToken.source();

            const params = {...this.parameters, ...{
                search: this.searchQuery,
            }};

            return this.$axios.get(this.selectionsUrl, { params, cancelToken: this.source.token }).then(response => {
                this.columns = response.data.meta.columns;
                this.items = response.data.data;
                this.meta = response.data.meta;
                this.activeFilterBadges = {...response.data.meta.activeFilterBadges};
                this.loading = false;
                this.initializing = false;
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
                this.loading = false;
                this.initializing = false;
                this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), { duration: null });
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        setPage(page) {
            this.page = page;
        },

        select() {
            this.$emit('selected', this.selections);
            this.close()
        },

        close() {
            this.$emit('closed');
        },

        selectionsUpdated(selections) {
            this.selections = selections;
        },

        getStatusClass(entry) {
            if (entry.published && entry.private) {
                return 'bg-transparent border border-gray-600';
            } else if (entry.published) {
                return 'bg-green-600';
            } else {
                return 'bg-gray-400';
            }
        },

        getStatusLabel(entry) {
            if (entry.status === 'published') {
                return __('Published');
            } else if (entry.status === 'scheduled') {
                return __('Scheduled');
            } else if (entry.status === 'expired') {
                return __('Expired');
            } else if (entry.status === 'draft') {
                return __('Draft');
            }
        },

        getStatusTooltip(entry) {
            if (entry.status === 'published') {
                return entry.collection.dated
                    ? __('messages.status_published_with_date', {date: entry.date})
                    : null; // The label is sufficient.
            } else if (entry.status === 'scheduled') {
                return __('messages.status_scheduled_with_date', {date: entry.date})
            } else if (entry.status === 'expired') {
                return __('messages.status_expired_with_date', {date: entry.date})
            } else if (entry.status === 'draft') {
                return null; // The label is sufficient.
            }
        },

        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
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

            if (! this.reachedSelectionLimit) {
                this.selections.push(id);
            }
        },

        checkboxClicked(row, index, $event) {
            if ($event.shiftKey && this.lastItemClicked !== null) {
                this.selectRange(
                    Math.min(this.lastItemClicked, index),
                    Math.max(this.lastItemClicked, index)
                );
            } else {
                this.toggleSelection(row.id, index)
            }

            if ($event.target.checked) {
                this.lastItemClicked = index
            }
        },

    }

}
</script>
