<template>
    <div>
        <!-- todo: max selections, create option -->
        <Combobox
            class="w-full"
            searchable
            :options
            :multiple
            option-value="id"
            option-label="title"
            :taggable="isTaggable"
            :disabled="readOnly"
            :ignore-filter="typeahead"
            :placeholder="__(config.placeholder) || __('Choose...')"
            :model-value="items.map((item) => item.id)"
            @update:selectedOptions="input"
            @search="search"
        >
            <template #option="{ title, hint, status }">
                <div class="flex w-full items-center justify-between">
                    <div class="flex items-center">
                        <StatusIndicator v-if="status" class="ltr:mr-2 rtl:ml-2" :status="status" />
                        <div v-text="title" />
                    </div>
                    <div v-if="hint" class="text-3xs whitespace-nowrap text-gray-600 uppercase" v-text="hint" />
                </div>
            </template>
            <template #no-options>
                <div v-text="noOptionsText" />
            </template>
            <template #selected-options>
                <!-- We don't need to display the selected options here. The RelationshipInput component does that for us. -->
                <div></div>
            </template>
        </Combobox>
    </div>
</template>

<script>
import { Combobox, StatusIndicator } from '@statamic/ui';

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
        config: Object,
        readOnly: Boolean,
        site: String,
    },

    data() {
        return {
            requested: false,
            options: [],
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

        noOptionsText() {
            return this.typeahead && !this.requested ? __('Start typing to search.') : __('No options to choose from.');
        },
    },

    created() {
        // Get the items via ajax.
        // TODO: To save on requests, this should probably be done in the preload step and sent via meta.
        if (!this.typeahead) this.request();
    },

    watch: {
        parameters(params) {
            if (!this.typeahead) this.request();
        },
    },

    methods: {
        request(params = {}) {
            params = { ...this.parameters, ...params };

            return this.$axios.get(this.url, { params }).then((response) => {
                this.options = response.data.data;
                this.requested = true;
                return Promise.resolve(response);
            });
        },

        search(search, loading) {
            if (!this.typeahead) return;

            loading(true);

            this.request({ search }).then((response) => loading(false));
        },

        input(items) {
            if (!this.multiple) {
                items = items === null ? [] : [items];
            }

            this.$emit('input', items);
        },

        createOption(value) {
            const existing = this.options.find((option) => option.title === value);
            return existing || { id: value, title: value };
        },
    },
};
</script>
