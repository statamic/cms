<script>
import createContext from '@statamic/util/createContext.js';

export const [injectListingContext, provideListingContext] = createContext('Listing');
</script>

<script setup>
import { ref, toRef, computed, watch, nextTick, onMounted, onBeforeUnmount, useSlots } from 'vue';
import { Icon, Panel, PanelFooter } from '@statamic/cms/ui';
import axios from 'axios';
import BulkActions from './BulkActions.vue';
import uniqid from 'uniqid';
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
    url: {
        type: String,
    },
    items: {
        type: Array,
    },
    allowPresets: {
        type: Boolean,
        default: true,
    },
    allowBulkActions: {
        type: Boolean,
        default: true,
    },
    actionUrl: {
        type: String,
    },
    actionContext: {
        type: Object,
        default: () => ({}),
    },
    allowActionsWhileReordering: {
        type: Boolean,
        default: false,
    },
    reorderable: {
        type: Boolean,
        default: false,
    },
    preferencesPrefix: {
        type: String,
    },
    columns: {
        type: Array,
    },
    allowCustomizingColumns: {
        type: Boolean,
        default: true,
    },
    sortColumn: {
        type: String,
        default: '',
    },
    sortDirection: {
        type: String,
        default: 'asc',
    },
    sortable: {
        type: Boolean,
        default: true,
    },
    selections: {
        type: Array,
    },
    maxSelections: {
        type: Number,
        default: Infinity,
    },
    pushQuery: {
        type: Boolean,
        default: false,
    },
    additionalParameters: {
        type: Object,
        default: () => ({}),
    },
    allowSearch: {
        type: Boolean,
        default: true,
    },
    searchQuery: {
        type: String,
        default: null,
    },
    filters: {
        type: Array,
        default: () => [],
    },
    filtersForReordering: {
        type: Function,
        default: null,
    },
    perPage: {
        type: Number,
        default: 15,
    },
    showPaginationTotals: {
        type: Boolean,
        default: true,
    },
    showPaginationPageLinks: {
        type: Boolean,
        default: true,
    },
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
    currentPage.value = parseInt(params.page);
    perPage.value = parseInt(params.perPage);
    sortColumn.value = params.sort;
    sortDirection.value = params.order;
    searchQuery.value = params.search;
    columns.value = columns.value.map((column) => ({
        ...column,
        visible: params.columns.split(',').includes(column.field),
    }));
    activeFilters.value = params.filters ? JSON.parse(utf8atob(params.filters)) : {};
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
    <slot name="initializing" v-if="initializing">
        <div class="flex flex-col gap-4 justify-between mt-2">
            <ui-skeleton class="h-3 w-48" />
            <div class="flex gap-3">
                <ui-skeleton class="h-8 w-80" />
                <ui-skeleton class="h-8 w-24" />
                <div class="flex-1" />
                <ui-skeleton class="size-8" />
            </div>
            <ui-skeleton class="h-48 w-full" />
        </div>
    </slot>
    <slot v-if="!initializing" :items="items" :is-column-visible="isColumnVisible" :loading="loading">
        <Presets v-if="showPresets" />
        <div v-if="allowSearch || hasFilters || allowCustomizingColumns" class="relative flex items-center gap-3 min-h-16">
            <div class="flex flex-1 items-center gap-3 w-full">
                <Search v-if="allowSearch" />
                <Filters v-if="hasFilters" />
            </div>
            <CustomizeColumns v-if="allowCustomizingColumns" />
        </div>

        <div
            v-if="!items.length"
            class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500"
            v-text="__('No results')"
        />

        <Panel v-else class="relative overflow-x-auto overscroll-x-contain">
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
