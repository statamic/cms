<template>

    <div class="relationship-input">
        <loading-graphic v-if="initializing" :inline="true" />

        <template v-if="!initializing">
            <div ref="items" class="relationship-input-items outline-none">
                <component
                    :is="itemComponent"
                    v-for="(item, i) in items"
                    :key="item.id"
                    :item="item"
                    :status-icon="statusIcons"
                    :editable="canEdit"
                    :sortable="canReorder"
                    class="item outline-none"
                    @removed="remove(i)"
                />
            </div>

            <div class="text-xs text-grey" v-if="maxItemsReached">
                <span>{{ __('Maximum items selected:')}}</span>
                <span>{{ maxItems }}/{{ maxItems }}</span>
            </div>
            <div v-else class="relative" :class="{ 'mt-2': items.length > 0 }" >
                <div class="flex flex-wrap items-center text-sm -mb-1">
                    <div class="relative mb-1">
                        <button v-if="canCreate" class="text-button text-blue hover:text-grey-80 mr-3 flex items-center outline-none" @click="isCreating = true">
                            <svg-icon name="content-writing" class="mr-sm h-4 w-4 flex items-center"></svg-icon>
                            {{ __('Create & Link Item') }}
                        </button>
                        <inline-create-form
                            v-if="isCreating"
                            :site="site"
                            @created="itemCreated"
                            @closed="stopCreating"
                        />
                    </div>
                    <button class="text-blue hover:text-grey-80 flex mb-1 outline-none" @click.prevent="isSelecting = true">
                        <svg-icon name="hyperlink" class="mr-sm h-4 w-4 flex items-center"></svg-icon>
                        {{ __('Link Existing Item') }}
                    </button>
                </div>
            </div>

            <stack name="item-selector" v-if="isSelecting" @closed="isSelecting = false">
                <item-selector
                    slot-scope="{ close }"
                    :url="selectionsUrl"
                    :site="site"
                    initial-sort-column="title"
                    initial-sort-direction="asc"
                    :initial-selections="selections"
                    :initial-columns="columns"
                    :max-selections="maxItems"
                    :search="search"
                    :can-create="canCreate"
                    @selected="selectionsUpdated"
                    @closed="close"
                />
            </stack>

            <input v-if="name" type="hidden" :name="name" :value="JSON.stringify(value)" />
        </template>
    </div>

</template>

<script>
import Popper from 'vue-popperjs';
import RelatedItem from './Item.vue';
import ItemSelector from './Selector.vue';
import {Sortable, Plugins} from '@shopify/draggable';
import InlineCreateForm from './InlineCreateForm.vue';

export default {

    props: {
        name: String,
        value: { required: true },
        initialData: Array,
        maxItems: Number,
        itemComponent: {
            type: String,
            default: 'RelatedItem',
        },
        itemDataUrl: String,
        selectionsUrl: String,
        statusIcons: Boolean,
        columns: Array,
        site: String,
        search: Boolean,
        canEdit: Boolean,
        canCreate: Boolean,
        canReorder: Boolean,
    },

    components: {
        Popper,
        ItemSelector,
        RelatedItem,
        InlineCreateForm
    },

    data() {
        return {
            isSelecting: false,
            isCreating: false,
            selections: this.value,
            itemData: [],
            initializing: true,
            loading: true,
            inline: false,
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
            this.$emit('input', this.selections);
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(`relationship-fieldtype-${this._uid}`, loading);
            }
        },

    },

    methods: {

        remove(index) {
            this.selections.splice(index, 1);
        },

        selectionsUpdated(selections) {
            this.getDataForSelections(selections);
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

            return this.$axios.get(this.itemDataUrl, { params }).then(response => {
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
                handle: '.item-move',
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

        itemCreated(item) {
            this.selections.push(item.id);
            this.itemData.push(item);
            this.stopCreating();
        },

        stopCreating() {
            this.isCreating = false;
        }

    }

}
</script>
