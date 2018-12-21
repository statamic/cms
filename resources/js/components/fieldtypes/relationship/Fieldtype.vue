<template>

    <div>
        <loading-graphic v-if="initializing" :inline="true" />

        <div v-if="!initializing">
            <div ref="items" class="outline-none">
                <related-item
                    v-for="(item, i) in items"
                    :key="item.id"
                    :item="item"
                    class="item outline-none"
                    @removed="remove(i)"
                />
            </div>

            <button
                v-if="!maxItemsReached"
                class="btn btn-sm"
                :class="{ 'mt-1': items.length > 0 }"
                @click.prevent="isSelecting = true"
                v-text="__('Add Item')" />

            <portal to="modals" v-if="isSelecting">
                <item-selector
                    :url="selectionsUrl"
                    initial-sort-column="title"
                    initial-sort-direction="asc"
                    :initial-selections="selections"
                    :max-selections="maxItems"
                    @selected="selectionsUpdated"
                    @closed="isSelecting = false"
                />
            </portal>
        </div>
    </div>

</template>

<style>
.relationship-fieldtype .item.draggable-source--is-dragging {
    opacity: 0.5;
}
</style>


<script>
import qs from 'qs';
import axios from 'axios';
import RelatedItem from './Item.vue';
import ItemSelector from './Selector.vue';
import {Sortable, Plugins} from '@shopify/draggable';

export default {

    mixins: [Fieldtype],

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
            inline: false
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

        maxItems() {
            return this.config.max_items || Infinity;
        },

        maxItemsReached() {
            return this.selections.length >= this.maxItems;
        },

        selectionsUrl() {
            return cp_url(`relationship-fieldtype`) + '?' + qs.stringify(this.selectionsUrlParameters);
        },

        selectionsUrlParameters() {
            let params = {};

            if (this.config.collections) {
                params.collections = this.config.collections;
            }

            return params;
        }

    },

    mounted() {
        this.getDataForSelections(this.selections)
            .then(() => this.makeSortable());
    },

    watch: {

        selections(selections) {
            this.update(selections);
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(`relationship-fieldtype-${this._uid}`, loading);
            }
        }

    },

    methods: {

        remove(index) {
            this.selections.splice(index, 1);
        },

        selectionsUpdated(selections) {
            this.getDataForSelections(selections);
        },

        getDataForSelections(selections) {
            this.loading = true;
            const url = cp_url(`relationship-fieldtype/data`);
            const params = { selections };

            return axios.get(url, { params }).then(response => {
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
        }

    }

}
</script>
