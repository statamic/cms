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
            :placeholder="fieldPlaceholder"
            :read-only="readOnly"
            :taggable="isTaggable"
            :close-on-select="isTaggable"
            :search-keys="searchKeys"
            option-label="title"
            option-value="id"
            @update:modelValue="itemsSelected"
            @search="search"
        >
            <template #option="{ title, hint, status, depth, path, _created }">
                <div
                    v-if="_created"
                    class="flex w-full min-w-0 text-left items-center gap-1.5"
                >
                    <span class="text-xs text-gray-600 dark:text-gray-400 shrink-0" v-text="__('Create')" />
                    <span v-text="title" class="truncate" />
                </div>
                <!--
                    A depth means the list is in tree order, so the ancestors are listed above and an
                    indent locates the option against them. Without one they aren't, and neither are
                    they while a query is filtering ancestors back out, so the option carries its own
                    breadcrumb instead.
                -->
                <div
                    v-else
                    class="flex w-full text-left items-center gap-2"
                    :style="!isFiltering && depth > 1 ? { paddingInlineStart: `${(depth - 1) * .75}rem` } : null"
                >
                    <ui-icon
                        v-if="!isFiltering && depth > 1"
                        name="arrow-down-right"
                        class="size-[14px] shrink-0 text-gray-400 dark:text-gray-600"
                        aria-hidden="true"
                    />
                    <StatusIndicator v-if="status" :status="status" />
                    <ItemPath v-if="isFiltering || !depth" :path="path" />
                    <div v-text="title" class="truncate grow" />
                    <ui-badge v-if="hint && !(depth > 1)" size="sm" v-text="hint" />
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
import ItemPath from './ItemPath.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const optionsCache = ref({});
const loaders = ref({});

export default {
    components: {
        StatusIndicator,
        Combobox,
        ItemPath,
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
        searchKeys: { type: Array, default: null },
        pathDelimiter: { type: String, default: null },
    },

    data() {
        return {
            requested: false,
            query: '',
            options: [],
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

        fieldPlaceholder() {
            if (this.config.placeholder) return __(this.config.placeholder);
            if (this.isTaggable) return __('Search or create...');

            return __('Choose...');
        },

        parameters() {
            return {
                site: this.site,
                paginate: false,
                columns: 'title,id',
            };
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

        // In select mode the combobox filters the fetched list itself, which can leave an option's
        // ancestors out of it. The server only knows to drop the depth when it can see the query,
        // which it never does in that mode, so the depth is suppressed here instead.
        isFiltering() {
            return this.query !== '';
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

        // The combobox emits this on every query change, including in select mode where it
        // filters client-side and there is nothing to request.
        search(search, loading) {
            this.query = search;

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

                return existing || option || this.newItemFromId(id);
            });

            this.$emit('input', items);
        },

        // A typed path like `animals > cat > calico` attaches the leaf, so give it the same
        // ancestor breadcrumb a saved item gets until the save fills one in for real.
        newItemFromId(id) {
            if (this.pathDelimiter && typeof id === 'string' && id.includes(this.pathDelimiter)) {
                const segments = id.split(this.pathDelimiter).map((segment) => segment.trim()).filter(Boolean);
                const title = segments.pop();

                if (title && segments.length) {
                    return { id, title, path: segments };
                }
            }

            return { id: id, title: id };
        },

        createOption(value) {
            const existing = this.options.find((option) => option.title === value);
            return existing || { id: value, title: value };
        },
    },
};
</script>
