<script>
import createContext from '@/util/createContext.js';

export const [injectListingContext, provideListingContext] = createContext('Listing');
</script>

<script setup>
import { ref, toRef, computed, watch, nextTick, onMounted, onBeforeUnmount, useSlots } from 'vue';
import useSkeletonDelay from '@/composables/skeleton-delay.js';
import {
    Icon,
    Panel,
    PanelFooter,
} from '@ui';
import axios from 'axios';
import BulkActions from './BulkActions.vue';
import { nanoid as uniqid } from 'nanoid';
import CustomizeColumns from './CustomizeColumns.vue';
import Presets from './Presets.vue';
import Search from './Search.vue';
import Filters from './Filters.vue';
import Table from './Table.vue';
import Pagination from './Pagination.vue';
import { sortBy } from 'lodash-es';
import fuzzysort from 'fuzzysort';

const emit = defineEmits([
    'update:columns',
    'update:sortColumn',
    'update:sortDirection',
    'update:selections',
    'update:searchQuery',
    'requestCompleted',
    'reordered',
    'refreshing',
]);

const props = defineProps({
    /** The URL from which to retrieve results. Either use this or `items`. */
    url: {
        type: String,
    },
    /** Array of items to display in the listing. When provided, sorting and filtering is done client-side. Either use this or `url`. */
    items: {
        type: Array,
    },
    /** When `true`, allows users to save and load column/filter presets. */
    allowPresets: {
        type: Boolean,
        default: true,
    },
    /** When `true`, bulk actions are available when items are selected. */
    allowBulkActions: {
        type: Boolean,
        default: true,
    },
    /** The URL from which to retrieve actions. */
    actionUrl: {
        type: String,
    },
    /** Extra data to pass to the server when using actions. */
    actionContext: {
        type: Object,
        default: () => ({}),
    },
    /** When `true`, enables the action dropdown while reordering is enabled. */
    allowActionsWhileReordering: {
        type: Boolean,
        default: false,
    },
    /** When `true`, adds drag handles to the rows. */
    reorderable: {
        type: Boolean,
        default: false,
    },
    /** Any preferences (preferred columns, etc) will be saved nested under this. */
    preferencesPrefix: {
        type: String,
    },
    /** The columns to display. Can be array of strings or column definitions. */
    columns: {
        type: Array,
    },
    /** When `true`, users can show/hide columns. */
    allowCustomizingColumns: {
        type: Boolean,
        default: true,
    },
    /** Defines the sort column. */
    sortColumn: {
        type: String,
        default: '',
    },
    /** Defines the sort direction. Defaults to `asc` for most fields, `desc` for dates. <br><br> Options: `asc`, `desc` */
    sortDirection: {
        type: String,
        default: 'asc',
    },
    /** When `true`, columns can be sorted by clicking on headers. */
    sortable: {
        type: Boolean,
        default: true,
    },
    /** Array of checked item IDs. */
    selections: {
        type: Array,
    },
    /** Maximum number of items that can be selected. */
    maxSelections: {
        type: Number,
        default: Infinity,
    },
    /** When `true`, adds the parameters to the current URL. */
    pushQuery: {
        type: Boolean,
        default: false,
    },
    /** Extra data to send to the AJAX URL. */
    additionalParameters: {
        type: Object,
        default: () => ({}),
    },
    /** When `true`, displays a search input for filtering items. */
    allowSearch: {
        type: Boolean,
        default: true,
    },
    /** The search query value. */
    searchQuery: {
        type: String,
        default: null,
    },
    /** Array of filter definitions. You can get this by doing `Scope::filters($name, $context)` */
    filters: {
        type: Array,
        default: () => [],
    },
    /** A function that returns array of filter values to be activated when reordering is enabled. */
    filtersForReordering: {
        type: Function,
        default: null,
    },
    /** Number of items to display per page. */
    perPage: {
        type: Number,
        default: 15,
    },
    /** When `true`, shows the totals in the paginator. e.g. "1-5 of 10" */
    showPaginationTotals: {
        type: Boolean,
        default: true,
    },
    /** When `true`, shows the page links. e.g. 1,2,3,4. With this disabled you'll just get the prev/next arrows. */
    showPaginationPageLinks: {
        type: Boolean,
        default: true,
    },
    /** When `true`, shows the per page dropdown. */
    showPaginationPerPageSelector: {
        type: Boolean,
        default: true,
    },
});

const slots = useSlots();
const id = uniqid();
const rawItems = ref(props.items);
const meta = ref();
const activeFilters = ref({});
const activeFilterBadges = ref([]);
const stateBeforeReordering = ref(null);
const currentPage = ref(1);
const perPage = ref(initializePerPage());
const initializing = ref(true);
const loading = ref(true);
let popping = false;
let source = null;
const searchQuery = ref(null);
const columns = ref(initializeColumns());
const sortColumn = ref(props.sortColumn || (columns.value.length ? columns.value[0].field : null));
const sortDirection = ref(props.sortDirection || getDefaultSortDirectionForColumn(sortColumn.value));
const selections = ref(props.selections || []);
const allowsSelections = computed(() => (props.selections || hasActions.value) && !props.reorderable);
const allowsMultipleSelections = computed(() => props.maxSelections > 1);
const hasReachedSelectionLimit = computed(() => selections.value.length === props.maxSelections);
const hasActions = computed(() => !!props.actionUrl);
const hasFilters = computed(() => props.filters && props.filters.length > 0);
const showPresets = computed(() => props.allowPresets && props.preferencesPrefix);
const showBulkActions = computed(() => props.allowBulkActions && hasActions.value);
const shouldShowSkeleton = useSkeletonDelay(initializing);

const items = computed({
    get() {
        let items = rawItems.value;

        // If items are provided as a prop, we will sort and filter them locally.
        // Otherwise, they will be fetched from the server.
        if (!props.items) return items;

        if (searchQuery.value) {
            items = fuzzysort
                .go(searchQuery.value, items, {
                    all: true,
                    keys: visibleColumns.value.map((c) => c.field),
                })
                .map((result) => result.obj);
        }

        if (props.sortable) {
            items = sortBy(items, (obj) => {
                let value = obj[sortColumn.value];
                if (typeof value === 'string') value = value.toLowerCase();
                return value;
            });
        }

        return sortDirection.value === 'desc' ? items.reverse() : items;
    },
    set(newItems) {
        rawItems.value = newItems;
    },
});

watch(
    () => props.items,
    (items) => rawItems.value = items,
);

watch(
    () => props.selections,
    () => {
        if (JSON.stringify(props.selections) === JSON.stringify(selections.value)) {
            return;
        }

        selections.value = props.selections || [];
    }
);

const rawParameters = computed(() => ({
    page: currentPage.value,
    perPage: perPage.value,
    sort: sortColumn.value,
    order: sortDirection.value,
    search: searchQuery.value,
    columns: visibleColumns.value.map((column) => column.field).join(','),
    filters: Object.keys(activeFilters.value).length === 0 ? null : utf8btoa(JSON.stringify(activeFilters.value)),
}));

watch(columns, (newColumns) => emit('update:columns', newColumns));
watch(sortColumn, (newSortColumn) => emit('update:sortColumn', newSortColumn));
watch(sortDirection, (newSortDirection) => emit('update:sortDirection', newSortDirection));
watch(selections, (newSelections) => emit('update:selections', newSelections), { deep: true });
watch(searchQuery, (newSearchQuery) => emit('update:searchQuery', newSearchQuery));

const forwardedTableCellSlots = computed(() => {
    return Object.keys(slots)
        .filter((slotName) => slotName.startsWith('cell-'))
        .reduce((acc, slotName) => {
            acc[slotName] = slots[slotName];
            return acc;
        }, {});
});

const activeFilterBadgeCount = computed(() => {
    let count = Object.keys(activeFilterBadges.value).length;

    if (activeFilterBadges.value.hasOwnProperty('fields')) {
        count = count + Object.keys(activeFilterBadges.value.fields).length - 1;
    }

    return count;
});

function setParameters(params) {
    if (params.hasOwnProperty('page')) currentPage.value = parseInt(params.page);
    if (params.hasOwnProperty('perPage')) perPage.value = parseInt(params.perPage);
    if (params.hasOwnProperty('sort')) sortColumn.value = params.sort;
    if (params.hasOwnProperty('order')) sortDirection.value = params.order;
    if (params.hasOwnProperty('search')) searchQuery.value = params.search;
    if (params.hasOwnProperty('columns')) {
        columns.value = columns.value.map((column) => ({
            ...column,
            visible: params.columns.split(',').includes(column.field),
        }));
    }
    if (params.hasOwnProperty('filters')) {
        activeFilters.value = params.filters ? JSON.parse(utf8atob(params.filters)) : {};
    }
}

const parameters = computed(() => {
    const params = Object.fromEntries(
        Object.entries(rawParameters.value).filter(([key, value]) => {
            return value !== null && value !== undefined && value !== '';
        }),
    );

    return { ...params, ...props.additionalParameters };
});

const shouldRequestFirstPage = computed(() => {
    if (currentPage.value > 1 && items.value.length === 0) {
        currentPage.value = 1;
        return true;
    }

    return false;
});

function request() {
    if (props.items) return;

    loading.value = true;

    if (source) source.abort();
    source = new AbortController();

    axios
        .get(props.url, {
            params: parameters.value,
            signal: source.signal,
        })
        .then((response) => {
            setColumns(response.data.meta.columns);
            activeFilterBadges.value = { ...response.data.meta.activeFilterBadges };
            items.value = Object.values(response.data.data);
            meta.value = response.data.meta;
            if (shouldRequestFirstPage.value) return request();
            initializing.value = false;
            loading.value = false;
            emit('requestCompleted', {
                response,
                items: items.value,
                parameters: parameters.value,
                activeFilters: activeFilters.value,
            });
        })
        .catch((e) => {
            if (axios.isCancel(e)) return;
            initializing.value = false;
            loading.value = false;
            if (e.request && !e.response) return;
            Statamic.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), {
                duration: null,
            });
        });
}

function refresh() {
    emit('refreshing');
    request();
}

function pushState() {
    if (!props.pushQuery || popping) return;

    // This ensures no additionalParameters are added to the URL
    const keys = Object.keys(rawParameters.value);
    const searchParams = new URLSearchParams(
        Object.fromEntries(
            keys.filter((key) => parameters.value.hasOwnProperty(key)).map((key) => [key, parameters.value[key]]),
        ),
    );

    window.history.pushState({ parameters: parameters.value }, '', '?' + searchParams.toString());
}

function popState(event) {
    if (!props.pushQuery || !event.state) return;

    popping = true;
    setParameters(event.state.parameters);
    nextTick(() => (popping = false));
}

function autoApplyState() {
    if (!props.pushQuery || !window.location.search) return;

    const searchParams = new URLSearchParams(window.location.search);
    const parameters = Object.fromEntries(searchParams.entries());
    popping = true;
    setParameters(parameters);
    nextTick(() => (popping = false));
}

const visibleColumns = computed(() => {
    const visibleColumns = columns.value.filter((column) => column.visible);
    return visibleColumns.length ? visibleColumns : columns.value;
});

const hiddenColumns = computed(() => columns.value.filter((column) => !column.visible));

const sortableColumns = computed(() => {
    return columns.value.filter((column) => column.sortable).map((column) => column.field);
});

function initializeColumns() {
    if (props.columns) {
        return props.columns.map((column) => {
            if (typeof column === 'string') {
                return { field: column, label: getColumnSentenceCaseLabel(column), sortable: true };
            }
            return column;
        });
    }

    if (props.items && props.items.length > 0) {
        return Object.keys(props.items[0] || {}).map((field) => ({
            field,
            label: getColumnSentenceCaseLabel(field),
            sortable: true,
        }));
    }

    return [];
}

function getColumnSentenceCaseLabel(field) {
    return __(
        field
            .replace(/_/g, ' ')
            .split(' ')
            .map((word) => getColumnSentenceCaseWord(word))
            .join(' '),
    );
}

function getColumnSentenceCaseWord(word) {
    return (
        {
            id: 'ID',
            url: 'URL',
        }[word] || word.charAt(0).toUpperCase() + word.slice(1)
    );
}

function isColumnVisible(column) {
    return visibleColumns.value.find((c) => c.field === column);
}

function setColumns(newColumns) {
    // Avoid unnecessary updates and infinite loops if the columns haven't changed.
    if (JSON.stringify(newColumns) === JSON.stringify(columns.value)) return;

    columns.value = newColumns;
}

function setSortColumn(column) {
    if (!props.sortable) return;

    if (!sortableColumns.value.includes(column)) return;

    // If sorting by the same column, toggle the direction.
    // Otherwise, set the default direction.
    if (sortColumn.value === column) {
        toggleSortDirection();
    } else {
        sortDirection.value = getDefaultSortDirectionForColumn(column);
    }

    sortColumn.value = column;
}

function getColumnFieldtype(column) {
    return columns.value.find((c) => c.field === column)?.fieldtype;
}

function getDefaultSortDirectionForColumn(column) {
    return getColumnFieldtype(column) === 'date' ? 'desc' : 'asc';
}

function toggleSortDirection() {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
}

function setCurrentPage(page) {
    currentPage.value = page;
}

function setPerPage(value) {
    perPage.value = value;

    if (props.preferencesPrefix) {
        Statamic.$preferences.set(props.preferencesPrefix + '.per_page', value);
    }
}

function initializePerPage() {
    let perPage = props.perPage;

    if (props.preferencesPrefix) {
        perPage = Statamic.$preferences.get(props.preferencesPrefix + '.per_page', perPage);
    }

    return perPage;
}

function setSearchQuery(query) {
    searchQuery.value = query;
}

function clearSearchQuery() {
    searchQuery.value = null;
}

let lastSelectionClicked = null;

function selectionClicked(index, event) {
    const item = items.value[index];

    if (event.shiftKey && lastSelectionClicked !== null) {
        selectRange(Math.min(lastSelectionClicked, index), Math.max(lastSelectionClicked, index));
    } else {
        toggleSelection(item.id);
    }

    if (selections.value.includes(item.id)) {
        lastSelectionClicked = index;
    }
}

function toggleSelection(id) {
    const i = selections.value.indexOf(id);

    if (i > -1) {
        selections.value.splice(i, 1);
        return;
    }

    if (!allowsMultipleSelections.value) selections.value.pop();

    if (!hasReachedSelectionLimit.value) selections.value.push(id);
}

function selectRange(from, to) {
    for (let i = from; i <= to; i++) {
        let row = items.value[i].id;
        if (!selections.value.includes(row) && !hasReachedSelectionLimit.value) {
            selections.value.push(row);
        }
    }
}

function clearSelections() {
    selections.value.splice(0, selections.value.length);
}

function setFilters(filters) {
    activeFilters.value = filters || {};
}

function setFilter(handle, values) {
    if (values) {
        activeFilters.value[handle] = values;
    } else {
        delete activeFilters.value[handle];
    }
}

function clearFilters() {
    activeFilters.value = {};
    activeFilterBadges.value = [];
}

function autoApplyFilters() {
    if (!props.filters || props.filters.length === 0) return;

    let values = {};

    const isEmpty = (value) => (Array.isArray(value) ? value.length === 0 : Object.keys(value).length === 0);

    props.filters
        .filter((filter) => !isEmpty(filter.auto_apply))
        .forEach((filter) => {
            values[filter.handle] = filter.auto_apply;
        });

    setFilters(values);
}

function reordered(order) {
	if (! props.items) {
		items.value = order;
	}

    emit('reordered', order);
}

watch(
    () => props.reorderable,
    (reorderable) => {
        if (reorderable) {
            stateBeforeReordering.value = {
                filters: activeFilters.value,
                search: searchQuery.value,
                sort: sortColumn.value,
                order: sortDirection.value,
            };
            activeFilters.value = props.filtersForReordering ? props.filtersForReordering() : {};
            searchQuery.value = null;
            sortColumn.value = 'order';
            sortDirection.value = 'asc';
            currentPage.value = 1;
        } else {
            if (stateBeforeReordering.value) {
                const { filters, search, sort, order } = stateBeforeReordering.value;
                activeFilters.value = filters;
                searchQuery.value = search;
                sortColumn.value = sort;
                sortDirection.value = order;
                stateBeforeReordering.value = null;
            }
        }
    },
);

provideListingContext({
    loading,
    refresh,
    items,
    meta,
    columns,
    setColumns,
    visibleColumns,
    isColumnVisible,
    hiddenColumns,
    sortColumn,
    sortDirection,
    setSortColumn,
    selections,
    maxSelections: toRef(() => props.maxSelections),
    allowsSelections,
    allowsMultipleSelections,
    hasReachedSelectionLimit,
    selectionClicked,
    selectRange,
    toggleSelection,
    clearSelections,
    actionUrl: toRef(() => props.actionUrl),
    actionContext: toRef(() => props.actionContext),
    showBulkActions,
    hasActions,
    allowActionsWhileReordering: toRef(() => props.allowActionsWhileReordering),
    perPage,
    setPerPage,
    setCurrentPage,
    showPaginationTotals: toRef(() => props.showPaginationTotals),
    showPaginationPageLinks: toRef(() => props.showPaginationPageLinks),
    showPaginationPerPageSelector: toRef(() => props.showPaginationPerPageSelector),
    searchQuery,
    setSearchQuery,
    clearSearchQuery,
    preferencesPrefix: toRef(() => props.preferencesPrefix),
    filters: toRef(() => props.filters),
    activeFilters,
    activeFilterBadges,
    activeFilterBadgeCount,
    setFilter,
    setFilters,
    clearFilters,
    reorderable: toRef(() => props.reorderable),
    reordered,
});

defineExpose({
    refresh,
    setFilter,
});

watch(parameters, (newParams, oldParams) => {
    if (JSON.stringify(newParams) === JSON.stringify(oldParams)) return;
    request();
    pushState();
});

watch(loading, (loading) => Statamic.$progress.loading(id, loading));

onMounted(() => {
    if (props.pushQuery) {
        window.history.replaceState({ parameters: parameters.value }, '');
        window.addEventListener('popstate', popState);
    }
});

onBeforeUnmount(() => {
    if (props.pushQuery) window.removeEventListener('popstate', popState);
});

autoApplyFilters();

if (props.items) {
    items.value = props.items;
    initializing.value = false;
    loading.value = false;
} else {
    request();
}

autoApplyState();
</script>

<template>
    <slot name="initializing" v-if="shouldShowSkeleton">
        <div class="flex flex-col gap-4 justify-between mt-3 starting-style-transition starting-style-transition--delay">
            <ui-skeleton class="h-5 w-48" />
            <div class="flex gap-2 sm:gap-3">
                <ui-skeleton class="h-9 w-96" />
                <ui-skeleton class="h-9 w-24" />
                <div class="flex-1" />
                <ui-skeleton class="size-10" />
            </div>
            <ui-skeleton class="h-48 w-full" />
        </div>
    </slot>
    <slot v-if="!initializing" :items="items" :is-column-visible="isColumnVisible" :loading="loading">
        <Presets v-if="showPresets" />
        <div v-if="allowSearch || hasFilters || allowCustomizingColumns" class="relative overflow-clip flex items-center gap-2 sm:gap-3 min-h-16 starting-style-transition st-overflow-clip-margin">
            <div class="flex flex-1 items-center gap-2 sm:gap-3 w-full">
                <Search v-if="allowSearch" />
                <Filters v-if="hasFilters" />
            </div>
            <CustomizeColumns v-if="allowCustomizingColumns" />
        </div>

        <div
            v-if="!items.length"
            class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-gray-500"
            v-text="__('No results')"
        />

        <Panel v-else class="relative overflow-x-auto overscroll-x-contain" style="container-type: scroll-state;">
            <Table>
                <template v-for="(slot, slotName) in forwardedTableCellSlots" :key="slotName" #[slotName]="slotProps">
                    <component :is="slot" v-bind="slotProps" />
                </template>
                <template v-if="$slots['prepended-row-actions']" #prepended-row-actions="{ row }">
                    <slot name="prepended-row-actions" :row="row" />
                </template>
            </Table>
            <PanelFooter v-if="meta">
                <Pagination />
            </PanelFooter>
        </Panel>
    </slot>
    <BulkActions v-if="showBulkActions" />
</template>
