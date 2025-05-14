<template>
    <div>
        <Combobox
            class="w-full"
            searchable
            :options
            :multiple
            option-value="id"
            option-label="title"
            :taggable="isTaggable"
            :max-selections="maxSelections"
            :disabled="readOnly"
            :ignore-filter="typeahead"
            :placeholder="__(config.placeholder) || __('Choose...')"
            :model-value="items.map((item) => item.id)"
            @update:modelValue="itemsSelected"
            @search="search"
        >
            <template #option="{ title, hint, status }">
                <div class="flex w-full items-center justify-between">
                    <div class="flex items-center">
                        <StatusIndicator v-if="status" class="me-2" :status="status" />
                        <div v-text="title" class="truncate" />
                    </div>
                    <ui-badge v-if="hint" size="sm" variant="flat" v-text="hint" />
                </div>
            </template>
            <template #no-options>
                <div v-text="noOptionsText" />
            </template>
            <template #selected-option>
                <span v-if="items.length === 1" v-text="items[0].title"></span>
            </template>
            <template #selected-options>
                <!-- We don't want to display the selected options here. The RelationshipInput component does that for us. -->
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
        maxSelections: Number,
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

        itemsSelected(items) {
            if (!this.multiple) {
                items = items === null ? [] : [items];
            }

            items = items.map((id) => {
                let option = this.options.find((option) => option.id === id);
                let existing = this.items.find((item) => item.id === id);

                return existing || option || { id: value, title: value };
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
