<template>

    <div>
        <v-select
            ref="input"
            label="title"
            :close-on-select="true"
            :disabled="readOnly"
            :multiple="multiple"
            :options="options"
            :get-option-key="(option) => option.id"
            :get-option-label="(option) => option.title"
            :create-option="(value) => ({ title: value, id: value })"
            :placeholder="config.placeholder || __('Choose...')"
            :searchable="true"
            :taggable="isTaggable"
            :value="items"
            @input="input"
            @search="search"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')"
        >
            <template #selected-option-container v-if="multiple"><i class="hidden"></i></template>
            <template #search="{ events, attributes }" v-if="multiple">
                <input
                    :placeholder="config.placeholder || __('Choose...')"
                    class="vs__search"
                    type="search"
                    v-on="events"
                    v-bind="attributes"
                >
            </template>
             <template #no-options>
                <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
            </template>
            <template #footer="{ deselect }" v-if="multiple">
                <sortable-list
                    item-class="sortable-item"
                    handle-class="sortable-item"
                    :value="items"
                    @input="input"
                >
                    <div class="vs__selected-options-outside flex flex-wrap">
                        <span v-for="item in items" :key="item.id" class="vs__selected mt-1" :class="{ 'sortable-item': !readOnly }">
                            {{ item.title }}
                            <button v-if="!readOnly" @click="deselect(item)" type="button" :aria-label="__('Deselect option')" class="vs__deselect">
                                <span>×</span>
                            </button>
                            <button v-else type="button" class="vs__deselect">
                                <span class="opacity-50">×</span>
                            </button>
                        </span>
                    </div>
                </sortable-list>
            </template>
        </v-select>
    </div>

</template>

<script>
import { SortableList, SortableItem } from '../../sortable/Sortable';

export default {

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
                paginate: false
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
        }

    }

}
</script>
