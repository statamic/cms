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
            @search:blur="$emit('blur')"
        >
            <template #option="{ title, hint, status }">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div v-if="status" class="little-dot rtl:ml-2 ltr:mr-2 hidden@sm:block" :class="status" />
                        <div v-text="title" />
                    </div>
                    <div v-if="hint" class="text-4xs text-gray-600 uppercase whitespace-nowrap" v-text="hint" />
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
                >
            </template>
             <template #no-options>
                <div class="text-sm text-gray-700 rtl:text-right ltr:text-left py-2 px-4" v-text="__('No options to choose from.')" />
            </template>
        </v-select>
    </div>

</template>

<style scoped>
    .draggable-source--is-dragging {
        @apply opacity-75 bg-transparent border-dashed
    }
</style>

<script>
import PositionsSelectOptions from '../../../mixins/PositionsSelectOptions';
import { SortableList, SortableItem } from '../../sortable/Sortable';

export default {

    mixins: [PositionsSelectOptions],

    components: {
        SortableList,
        SortableItem,
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
            options: [],
        }
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
            }
        }
    },

    created() {
        // Get the items via ajax.
        // TODO: To save on requests, this should probably be done in the preload step and sent via meta.
        if (! this.typeahead) this.request();
    },

    watch: {
        parameters(params) {
            if (! this.typeahead) this.request();
        }
    },

    methods: {

        request(params = {}) {
            params = {...this.parameters, ...params};

            return this.$axios.get(this.url, { params }).then(response => {
                this.options = response.data.data;
                return Promise.resolve(response);
            });
        },

        search(search, loading) {
            if (! this.typeahead) return;

            loading(true);

            this.request({ search }).then(response => loading(false));
        },

        input(items) {
            if (! this.multiple) {
                items = items === null ? [] : [items];
            }

            this.$emit('input', items);
        },

        createOption(value) {
            const existing = this.options.find((option) => option.title === value);
            return existing || { id: value, title: value };
        },

    }

}
</script>
