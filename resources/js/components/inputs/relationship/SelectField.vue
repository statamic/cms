<template>
    <div>
        <v-select
            ref="input"
            label="title"
            append-to-body
            :calculate-position="positionOptions"
            :close-on-select="true"
            :disabled="readOnly"
            :multiple="multiple"
            :options="options"
            :get-option-key="(option) => option.id"
            :get-option-label="(option) => __(option.title)"
            :create-option="(value) => createOption(value)"
            :placeholder="__(config.placeholder) || __('Choose...')"
            :searchable="true"
            :taggable="isTaggable"
            :model-value="items"
            @update:model-value="input"
            @search="search"
            @search:focus="$emit('focus')"
            @-search:blur-sm="$emit('blur-sm')"
        >
            <template #option="{ title, hint, status }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div v-if="status" class="little-dot hidden@sm:block ltr:mr-2 rtl:ml-2" :class="status" />
                        <div v-text="title" />
                    </div>
                    <div v-if="hint" class="whitespace-nowrap text-4xs uppercase text-gray-600" v-text="hint" />
                </div>
            </template>
            <template #selected-option-container v-if="multiple"><i class="hidden"></i></template>
            <template #search="{ events, attributes }" v-if="multiple">
                <input
                    :placeholder="__(config.placeholder) || __('Choose...')"
                    class="vs__search"
                    type="search"
                    v-on="events"
                    v-bind="attributes"
                />
            </template>
            <template #no-options>
                <div class="px-4 py-2 text-sm text-gray-700 ltr:text-left rtl:text-right" v-text="noOptionsText" />
            </template>
        </v-select>
    </div>
</template>

<style scoped>
.draggable-source--is-dragging {
    @apply border-dashed bg-transparent opacity-75;
}
</style>

<script>
import PositionsSelectOptions from '../../../mixins/PositionsSelectOptions';
import { SortableList } from '../../sortable/Sortable';

export default {
    mixins: [PositionsSelectOptions],

    components: {
        SortableList,
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
