<template>
    <div>
        <Combobox
            searchable
            :disabled="config.disabled"
            :ignore-filter="typeahead"
            :max-selections="maxSelections"
            :model-value="items.map((item) => item.id)"
            :multiple
            :options="comboboxOptions"
            :placeholder="__(config.placeholder) || __('Choose...')"
            :read-only="readOnly"
            :taggable="isTaggable"
            :close-on-select="isTaggable"
            :search-keys="searchKeys"
            option-label="title"
            option-value="id"
            @update:modelValue="itemsSelected"
            @search="search"
        >
            <template #option="{ title, hint, status }">
                <div class="flex w-full text-left items-center gap-2">
                    <StatusIndicator v-if="status" :status="status" />
                    <div v-text="title" class="truncate grow" />
                    <ui-badge v-if="hint" size="sm" v-text="hint" />
                </div>
            </template>
            <template #no-options>
                <div v-text="noOptionsText" />
            </template>
            <template #selected-option>
                <span v-if="items.length === 1" v-text="items[0].title" class="truncate"></span>
            </template>
            <template #selected-options>
                <!-- We don't want to display the selected options here. The RelationshipInput component does that for us. -->
                <div></div>
            </template>
        </Combobox>
    </div>
</template>

<script>
import { Combobox, StatusIndicator } from '@/components/ui';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

// Settled options, scoped to the current page view (cleared on Inertia navigation).
const optionsCache = ref({});
const inFlightRequests = new Map();
let navigationListenerAttached = false;

function ensureCacheClearedOnNavigation() {
    if (navigationListenerAttached) return;
    navigationListenerAttached = true;
    router.on('before', () => {
        optionsCache.value = {};
    });
}

function detachFromInFlightRequest(component) {
    const entry = component._activeRequest;
    if (!entry) return;
    component._activeRequest = null;
    entry.subscribers--;
    if (entry.subscribers > 0) return;
    entry.controller.abort();
    if (inFlightRequests.get(entry.cacheKey) === entry) {
        inFlightRequests.delete(entry.cacheKey);
    }
}

export default {
    components: {
        StatusIndicator,
        Combobox,
    },

    props: {
        items: Array,
        url: String,
        typeahead: Boolean,
        multiple: Boolean,
        taggable: Boolean,
        maxSelections: Number,
        config: Object,
        readOnly: Boolean,
        site: String,
    },

    data() {
        return {
            requested: false,
            options: [],
            removeNavigationListener: null,
        };
    },

    emits: ['input'],

    computed: {
        isTaggable() {
            if (data_get(this.config, 'create') === false) return false;

            return this.taggable;
        },

        parameters() {
            return {
                site: this.site,
                paginate: false,
                columns: 'title,id',
            };
        },

        // The `users` fieldtype falls back to displaying a user's email as their title when
        // they have no name, but doesn't show it otherwise, so it needs to be searchable too.
        searchKeys() {
            return this.config.type === 'users' ? ['title', 'email'] : null;
        },

        cacheKey() {
            return JSON.stringify({ ...this.parameters, url: this.url });
        },

        comboboxOptions() {
            // Combobox resolves the selected label from this list, so a selected item missing
            // from it (e.g. a just-created term) would otherwise display as its raw id.
            const missing = this.items.filter((item) => !this.options.some((option) => option.id === item.id));

            return [...this.options, ...missing];
        },

        noOptionsText() {
            return this.typeahead && !this.requested ? __('Start typing to search.') : __('No options to choose from.');
        },
    },

    created() {
        ensureCacheClearedOnNavigation();

        if (!this.typeahead) {
            const cached = optionsCache.value[this.cacheKey];
            if (cached) {
                this.options = cached;
                this.requested = true;
            } else {
                this.request();
            }
        }

        this.removeNavigationListener = router.on('before', () => {
            detachFromInFlightRequest(this);
        });
    },

    beforeUnmount() {
        detachFromInFlightRequest(this);
        if (this.removeNavigationListener) this.removeNavigationListener();
    },

    watch: {
        // Watch the primitive string key — not the `parameters` object, which is a fresh
        // literal every evaluation and would re-fire the request on identity alone.
        cacheKey() {
            if (!this.typeahead) this.request();
        },
    },

    methods: {
        request(params = {}) {
            const isSearch = Object.keys(params).length > 0;
            const requestParams = { ...this.parameters, ...params };
            const cacheKey = isSearch
                ? JSON.stringify({ ...requestParams, url: this.url })
                : this.cacheKey;

            // Settled cache — non-search only (typeahead searches always hit the network).
            if (!isSearch && optionsCache.value[cacheKey]) {
                this.options = optionsCache.value[cacheKey];
                this.requested = true;
                return Promise.resolve({ data: { data: this.options } });
            }

            detachFromInFlightRequest(this);

            let entry = inFlightRequests.get(cacheKey);

            if (!entry) {
                const controller = new AbortController();
                entry = { cacheKey, controller, subscribers: 0 };
                entry.promise = this.$axios
                    .get(this.url, { params: requestParams, signal: controller.signal })
                    .then((response) => {
                        if (!isSearch) {
                            optionsCache.value = {
                                ...optionsCache.value,
                                [cacheKey]: response.data.data,
                            };
                        }
                        return response;
                    })
                    .finally(() => {
                        if (inFlightRequests.get(cacheKey) === entry) {
                            inFlightRequests.delete(cacheKey);
                        }
                    });
                inFlightRequests.set(cacheKey, entry);
            }

            entry.subscribers++;
            this._activeRequest = entry;

            return entry.promise
                .then((response) => {
                    if (this._activeRequest !== entry) return;
                    this.options = response.data.data;
                    this.requested = true;
                    return response;
                })
                .catch((e) => {
                    if (axios.isCancel(e)) return;
                    if (this._activeRequest !== entry) return;
                    throw e;
                });
        },

        search(search, loading) {
            if (!this.typeahead) return;

            loading(true);

            this.request({ search }).then((response) => loading(false));
        },

        itemsSelected(items) {
            if (!this.multiple) {
                items = items === null ? [] : [items];
            }

            items = items.map((id) => {
                let option = this.options.find((option) => option.id === id);
                let existing = this.items.find((item) => item.id === id);

                return existing || option || { id: id, title: id };
            });

            this.$emit('input', items);
        },

        createOption(value) {
            const existing = this.options.find((option) => option.title === value);
            return existing || { id: value, title: value };
        },
    },
};
</script>
