<script>
import createContext from '@statamic/util/createContext.js';

export const [injectListingContext, provideListingContext] = createContext('Listing');
</script>

<script setup>
import { ref, toRef, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Icon } from '@statamic/ui';
import axios from 'axios';
import BulkActions from './BulkActions.vue';
import uniqid from 'uniqid';
import CustomizeColumns from './CustomizeColumns.vue';
import Presets from './Presets.vue';
import Search from './Search.vue';
import Filters from './Filters.vue';
import Table from './Table.vue';

const emit = defineEmits(['update:columns', 'update:sortColumn', 'update:sortDirection', 'update:selections']);

const props = defineProps({
    url: {
        type: String,
        required: true,
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
    reorderable: {
        type: Boolean,
        default: false,
    },
    preferencesPrefix: {
        type: String,
    },
    columns: {
        type: Array,
        default: () => [],
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
        default: () => [],
    },
    maxSelections: {
        type: Number,
        default: Infinity,
    },
    pushQuery: {
        type: Boolean,
        default: true,
    },
    additionalParameters: {
        type: Object,
        default: () => ({}),
    },
    searchQuery: {
        type: String,
        default: null,
    },
    filters: {
        type: Array,
        default: () => [],
    },
});

const id = uniqid();
const items = ref([]);
const meta = ref({});
const activeFilters = ref({});
const activeFilterBadges = ref([]);
const currentPage = ref(1);
const perPage = ref(10);
const initializing = ref(true);
const loading = ref(true);
let popping = false;
let source = null;
const searchQuery = ref(null);
const columns = ref(props.columns);
const sortColumn = ref(props.sortColumn || (props.columns.length ? props.columns[0].field : null));
const sortDirection = ref(props.sortDirection || getDefaultSortDirectionForColumn(sortColumn.value));
const selections = ref(props.selections || []);

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
}

function setSearchQuery(query) {
    searchQuery.value = query;
}

function clearSearchQuery() {
    searchQuery.value = null;
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

provideListingContext({
    loading,
    refresh,
    items,
    meta,
    columns,
    setColumns,
    visibleColumns,
    hiddenColumns,
    sortColumn,
    setSortColumn,
    selections,
    maxSelections: toRef(() => props.maxSelections),
    clearSelections,
    actionUrl: toRef(() => props.actionUrl),
    actionContext: toRef(() => props.actionContext),
    allowBulkActions: toRef(() => props.allowBulkActions),
    perPage,
    setPerPage,
    setCurrentPage,
    searchQuery,
    setSearchQuery,
    clearSearchQuery,
    preferencesPrefix: toRef(() => props.preferencesPrefix),
    filters: toRef(() => props.filters),
    activeFilters,
    activeFilterBadges,
    setFilter,
    setFilters,
    clearFilters,
});

const slotProps = computed(() => ({
    isColumnVisible,
}));

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

request();
autoApplyState();
</script>

<template>
    <slot name="initializing" v-if="initializing">
        <Icon name="loading" />
    </slot>
    <slot v-if="!initializing" v-bind="slotProps">
        <Presets />
        <div class="flex items-center gap-3">
            <Search />
            <Filters />
            <CustomizeColumns />
        </div>
        <Table />
    </slot>
    <BulkActions />
</template>
