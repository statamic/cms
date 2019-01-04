<template>

    <div class="relationship-input">
        <loading-graphic v-if="initializing" :inline="true" />

        <div v-if="!initializing">
            <div ref="items" class="outline-none">
                <related-item
                    v-for="(item, i) in items"
                    :key="item.id"
                    :item="item"
                    :status-icon="statusIcons"
                    :editable="editableItems"
                    class="item outline-none"
                    @removed="remove(i)"
                />
            </div>

            <div
                v-if="!maxItemsReached"
                class="relative"
                :class="{ 'mt-2': items.length > 0 }"
            >
                <button v-if="!searchable" class="btn btn-sm" @click.prevent="isSelecting = true">
                    {{ __('Add Item') }}
                </button>
                <template v-if="searchable">
                    <div class="flex items-center input-text p-0">
                        <input
                            type="text"
                            class="outline-none bg-transparent flex-1 px-1"
                            :value="searchQuery"
                            @input="updateSearchQuery"
                            @keydown.esc="searchQuery = ''"
                        />
                        <loading-graphic :inline="true" text="" v-if="loading" />
                        <div class="border-l h-full px-1 bg-grey-lightest flex items-center">
                            <button class="leading-none" @click.prevent="isSelecting = true">
                                <i class="icon icon-hair-cross text-grey-light" />
                            </button>
                        </div>
                    </div>
                    <div class="absolute text-2xs card p-1 w-full" v-if="searchQuery && !loading">
                        <div class="p-1 rounded" v-if="!loading && suggestions.length === 0">
                            {{ __('No results for ":searchQuery".', { searchQuery }) }}
                            <button @click="searchQuery = ''; isSelecting = true" class="text-blue">{{ __('View all') }}</button>.
                        </div>
                        <div v-for="item in suggestions"
                            :key="item.id"
                            class="p-1 rounded cursor-pointer hover:bg-grey-lighter"
                            @click="select(item)"
                        >
                            <div
                                v-if="statusIcons"
                                class="little-dot mr-1"
                                :class="{ 'bg-green': item.published, 'bg-grey-light': !item.published, 'bg-red': item.invalid }"
                            />
                            {{ item.title }}
                        </div>
                    </div>
                </template>
            </div>

            <item-selector
                v-if="isSelecting"
                :url="selectionsUrl"
                initial-sort-column="title"
                initial-sort-direction="asc"
                :initial-selections="selections"
                :initial-columns="columns"
                :max-selections="maxItems"
                @selected="selectionsUpdated"
                @closed="isSelecting = false"
            />
        </div>
    </div>

</template>

<style>
.relationship-input .item.draggable-source--is-dragging {
    opacity: 0.5;
}
</style>


<script>
import axios from 'axios';
import RelatedItem from './Item.vue';
import ItemSelector from './Selector.vue';
import {Sortable, Plugins} from '@shopify/draggable';

export default {

    props: {
        value: { required: true },
        initialData: Object,
        maxItems: Number,
        itemDataUrl: String,
        selectionsUrl: String,
        statusIcons: Boolean,
        editableItems: Boolean,
        columns: Array,
        searchable: Boolean
    },

    components: {
        ItemSelector,
        RelatedItem
    },

    data() {
        return {
            isSelecting: false,
            selections: this.value,
            itemData: [],
            initializing: true,
            loading: true,
            inline: false,
            searchQuery: '',
            suggestions: []
        }
    },

    computed: {

        items() {
            return this.selections.map(selection => {
                const data = _.findWhere(this.itemData, { id: selection });

                if (! data) return { id: selection, title: selection };

                return data;
            });
        },

        maxItemsReached() {
            return this.selections.length >= this.maxItems;
        },

    },

    mounted() {
        this.initializeData()
            .then(() => this.makeSortable());
    },

    watch: {

        selections(selections) {
            let value = this.selections;

            if (this.maxItems === 1) {
                value = value[0];
            }

            this.$emit('input', value);
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(`relationship-fieldtype-${this._uid}`, loading);
            }
        },

        searchQuery() {
            this.request();
        },

    },

    methods: {

        remove(index) {
            this.selections.splice(index, 1);
        },

        selectionsUpdated(selections) {
            this.getDataForSelections(selections);
        },

        select(item) {
            this.selections.push(item.id);
            this.$set(this.itemData, item.id, item);
            this.searchQuery = '';
        },

        initializeData() {
            if (!this.initialData) {
                return this.getDataForSelections(this.selections);
            }

            this.itemData = this.initialData;
            this.loading = false;
            this.initializing = false;
            return Promise.resolve();
        },

        getDataForSelections(selections) {
            this.loading = true;
            const params = { selections };

            return axios.get(this.itemDataUrl, { params }).then(response => {
                this.loading = false;
                this.initializing = false;

                this.itemData = response.data.data;
                this.selections = this.itemData.map(item => {
                    return item.id;
                });
            });
        },

        makeSortable() {
            new Sortable(this.$refs.items, {
                draggable: '.item',
                handle: '.item-inner',
                mirror: { constrainDimensions: true },
                swapAnimation: { vertical: true },
                plugins: [Plugins.SwapAnimation],
                delay: 200
            }).on('drag:start', e => {
                if (this.selections.length === 1) e.cancel();
            }).on('sortable:stop', e => {
                this.selections.splice(e.newIndex, 0, this.selections.splice(e.oldIndex, 1)[0]);
            });
        },

        updateSearchQuery: _.debounce(function (e) {
            this.searchQuery = e.target.value;
        }, 300),

        request() {
            if (! this.searchQuery) {
                this.suggestions = [];
                return;
            }

            this.loading = true;
            const params = { search: this.searchQuery };

            return axios.get(this.selectionsUrl, { params }).then(response => {
                this.suggestions = response.data.data.filter(suggestion => {
                    return !this.selections.includes(suggestion.id);
                });
                this.loading = false;
            });
        },

    }

}
</script>
