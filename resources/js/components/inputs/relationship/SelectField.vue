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
            <template #option="{ title, hint, status, depth, id, _created }">
                <div
                    v-if="_created || isTypedTermPath(id)"
                    class="flex w-full min-w-0 text-left items-center gap-1.5"
                >
                    <span class="text-xs text-gray-600 dark:text-gray-400 shrink-0" v-text="__('Create')" />
                    <template v-if="isTypedTermPath(id)">
                        <template v-for="(segment, i) in termPathSegments(title)" :key="i">
                            <span
                                v-if="i > 0"
                                class="text-xs text-gray-500 dark:text-gray-400"
                                aria-hidden="true"
                            >→</span>
                            <ui-badge size="sm" :text="segment" />
                        </template>
                    </template>
                    <span v-else v-text="title" class="truncate" />
                </div>
                <div
                    v-else
                    class="flex w-full text-left items-center gap-2"
                    :style="depth > 1 ? { paddingInlineStart: `${(depth - 1) * .75}rem` } : null"
                >
                    <ui-icon
                        v-if="depth > 1"
                        name="arrow-down-right"
                        class="size-[14px] shrink-0 text-gray-400 dark:text-gray-600"
                        aria-hidden="true"
                    />
                    <StatusIndicator v-if="status" :status="status" />
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
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const optionsCache = ref({});
const loaders = ref({});

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
        tree: Object,
    },

    data() {
        return {
            requested: false,
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

        // The `users` fieldtype falls back to displaying a user's email as their title when
        // they have no name, but doesn't show it otherwise, so it needs to be searchable too.
        // Terms in hierarchical taxonomies expose their slug path (e.g. `animals>cat`) so
        // searching a parent surfaces its descendants too.
        searchKeys() {
            if (this.config.type === 'users') return ['title', 'email'];
            if (this.config.type === 'terms') return ['title', 'path'];

            return null;
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

        // A typed term path like `animals > cat > calico` attaches the leaf, so render
        // the badge as `calico · animals » cat` until the save normalizes it.
        newItemFromId(id) {
            if (this.config.type === 'terms' && typeof id === 'string' && id.includes('>')) {
                const segments = id.split('>').map((segment) => segment.trim()).filter(Boolean);
                const title = segments.pop();

                if (title && segments.length) {
                    return { id, title, hint: segments.join(' » ') };
                }
            }

            return { id: id, title: id };
        },

        createOption(value) {
            const existing = this.options.find((option) => option.title === value);
            return existing || { id: value, title: value };
        },

        isTypedTermPath(id) {
            return this.config.type === 'terms'
                && typeof id === 'string'
                && id.includes('>')
                && !id.includes('::');
        },

        termPathSegments(title) {
            return String(title).split('>').map((segment) => segment.trim()).filter(Boolean);
        },
    },
};
</script>
