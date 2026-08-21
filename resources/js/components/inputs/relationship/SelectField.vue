<template>
    <div>
        <Combobox
            searchable
            :disabled="config.disabled"
            :ignore-filter="typeahead || hasNamedGroups"
            :max-selections="maxSelections"
            :model-value="items.map((item) => item.id)"
            :multiple
            :options="comboboxOptions"
            :placeholder="__(config.placeholder) || __('Choose...')"
            :read-only="readOnly"
            :taggable="isTaggable"
            :close-on-select="isTaggable"
            :search-keys="searchKeys"
            :virtualize="!hasNamedGroups"
            option-label="title"
            option-value="id"
            @update:modelValue="itemsSelected"
            @search="onSearch"
        >
            <template #before-option="option" v-if="hasNamedGroups">
                <div
                    v-if="option._showGroupSeparator"
                    class="mx-2 mb-2.25 mt-0.75 border-t border-gray-200 dark:border-gray-700"
                    role="separator"
                />
                <Subheading
                    v-if="option._groupLabel"
                    size="sm"
                    class="px-2.5 pb-1 pt-1.5 font-semibold uppercase tracking-wide text-gray-950 text-2xs dark:text-gray-300"
                    :text="__(option._groupLabel)"
                />
            </template>
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
import { Combobox, StatusIndicator, Subheading } from '@/components/ui';
import {
    flatOptionsFromSiteGroups,
    groupItemsBySiteGroup,
    hasNamedSiteGroups,
} from '@/util/site-groups.js';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import fuzzysort from 'fuzzysort';

const optionsCache = ref({});
const loaders = ref({});

export default {
    components: {
        StatusIndicator,
        Combobox,
        Subheading,
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
            searchQuery: '',
            abortController: null,
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

        rawOptions() {
            // Combobox resolves the selected label from this list, so a selected item missing
            // from it (e.g. a just-created term) would otherwise display as its raw id.
            const missing = this.items.filter((item) => !this.options.some((option) => option.id === item.id));

            return [...this.options, ...missing];
        },

        hasNamedGroups() {
            return hasNamedSiteGroups(this.rawOptions);
        },

        comboboxOptions() {
            if (!this.hasNamedGroups) {
                return this.rawOptions;
            }

            const query = this.searchQuery;

            return flatOptionsFromSiteGroups(groupItemsBySiteGroup(this.rawOptions), {
                filterItems: (items) => query
                    ? fuzzysort.go(query, items, { keys: ['title', 'group', 'id'] }).map((result) => result.obj)
                    : items,
            });
        },

        noOptionsText() {
            return this.typeahead && !this.requested ? __('Start typing to search.') : __('No options to choose from.');
        },
    },

    created() {
        if (!this.typeahead) this.request();

		watch(
			() => loaders.value[this.cacheKey],
			(loading) => {
				this.options = optionsCache[this.cacheKey];
				this.requested = true;
			}
		);

        this.removeNavigationListener = router.on('before', () => {
            if (this.abortController) this.abortController.abort();
        });
    },

    beforeUnmount() {
        if (this.abortController) this.abortController.abort();
        if (this.removeNavigationListener) this.removeNavigationListener();
    },

    watch: {
        parameters(params) {
            if (!this.typeahead) this.request();
        },
    },

    methods: {
        request(params = {}) {
			if (!Object.keys(params).length && loaders.value[this.cacheKey]) return Promise.resolve();

            params = { ...this.parameters, ...params };

			loaders.value = {...loaders.value, [this.cacheKey]: true};

            if (this.abortController) this.abortController.abort();
            this.abortController = new AbortController();

            return this.$axios.get(this.url, { params, signal: this.abortController.signal })
	            .then((response) => {
	                this.options = response.data.data;
	                this.requested = true;
		            optionsCache[this.cacheKey] = this.options;
	                return Promise.resolve(response);
	            })
	            .catch((e) => {
	                if (axios.isCancel(e)) return;
	                throw e;
	            })
	            .finally(() => {
					loaders.value = {...loaders.value, [this.cacheKey]: false};
	            });
        },

        search(search, loading) {
            if (!this.typeahead) return;

            loading(true);

            this.request({ search }).then((response) => loading(false));
        },

        onSearch(query, loading) {
            if (this.typeahead) {
                this.search(query, loading);

                return;
            }

            this.searchQuery = query;
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
