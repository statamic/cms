<template>

    <div>
        <loading-graphic v-if="initializing" :inline="true" />

        <div v-if="!initializing">
            <div
                v-for="(item, i) in items"
                :key="item.id"
                class="text-sm mb-1"
            >
                <div class="border shadow-inner bg-grey-lightest rounded-md leading-loose px-1 inline-flex items-center cursor-pointer">
                    <div class="little-dot bg-green mr-1" />
                    {{ item.title }}
                    <button
                        class="text-xs text-grey ml-1 font-bold outline-none hover:text-red"
                        @click.prevent="remove(i)">
                        &times;
                    </button>
                </div>
            </div>

            <button
                class="btn btn-sm"
                :class="{ 'mt-1': items.length > 0 }"
                @click.prevent="isSelecting = true"
                v-text="__('Add Item')" />

            <portal to="modals" v-if="isSelecting">
                <item-selector
                    initial-sort-column="title"
                    initial-sort-direction="asc"
                    :initial-selections="selections"
                    @selected="selectionsUpdated"
                    @closed="isSelecting = false"
                />
            </portal>
        </div>
    </div>

</template>

<script>
import axios from 'axios';
import ItemSelector from './Selector.vue';

export default {

    mixins: [Fieldtype],

    components: {
        ItemSelector
    },

    data() {
        return {
            isSelecting: false,
            selections: this.value,
            items: [],
            initializing: true,
            loading: true,
            inline: false
        }
    },

    created() {
        this.getData();
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
            this.items.splice(index, 1);
        },

        selectionsUpdated(selections) {
            this.selections = selections;
            this.getData();
        },

        getData() {
            this.loading = true;
            const url = cp_url(`relationship-fieldtype/data`);
            const params = { selections: this.selections };

            axios.get(url, { params }).then(response => {
                this.loading = false;
                this.initializing = false;
                this.items = response.data.data;
            });
        }

    }

}
</script>
