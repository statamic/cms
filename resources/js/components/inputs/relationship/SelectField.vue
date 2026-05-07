<template>
    <div>
        <Combobox
            searchable
            :disabled="config.disabled"
            :ignore-filter="typeahead"
            :max-selections="maxSelections"
            :model-value="items.map((item) => item.id)"
            :multiple
            :options
            :placeholder="__(config.placeholder) || __('Choose...')"
            :read-only="readOnly"
            :taggable="isTaggable"
            :close-on-select="isTaggable"
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
