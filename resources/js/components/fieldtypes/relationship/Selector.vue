<template>

    <modal name="item-selector" width="90%" height="90%">
        <div class="flex flex-col justify-end h-full">

            <div class="flex-1 flex flex-col">
                <data-list
                    v-if="!initializing"
                    :rows="items"
                    :columns="columns"
                    :sort="false"
                    :sort-column="sortColumn"
                    :sort-direction="sortDirection"
                    :selections="selections"
                    :max-selections="maxItems"
                    @selections-updated="selectionsUpdated"
                >
                    <div slot-scope="{ filteredRows: rows }" class="flex flex-col h-full justify-start">
                        <div class="data-list-header">
                            <data-list-toggle-all v-if="!maxItems" />
                            <data-list-search v-model="searchQuery" />
                        </div>
                        <div class="flex-1 overflow-scroll">
                            <data-table
                                :loading="loading"
                                :allow-bulk-actions="true"
                                @sorted="sorted"
                            >
                                <template slot="cell-url" slot-scope="{ row: entry }">
                                    <span class="text-2xs">{{ entry.url }}</span>
                                </template>
                            </data-table>
                        </div>
                    </div>
                </data-list>
            </div>

            <div class="p-2 border-t flex items-center justify-between bg-grey-lightest">
                <div class="text-sm text-grey-light"
                    v-text="maxItems
                        ? __n(':count/:max selected', selections, { max: maxItems })
                        : __n(':count selected', selections)"
                />
                <div>
                    <button
                        type="button"
                        class="btn"
                        @click="close"
                        v-text="__('Cancel')" />

                    <button
                        type="button"
                        class="btn btn-primary ml-1"
                        @click="select"
                        v-text="__('Select')" />
                </div>
            </div>

        </div>
    </modal>

</template>

<script>
import axios from 'axios';

export default {

    props: {
        initialSelections: Array,
        initialSortColumn: String,
        initialSortDirection: String,
        maxItems: Number
    },

    data() {
        return {
            initializing: true,
            loading: true,
            items: [],
            columns: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            page: 1,
            searchQuery: '',
            selections: this.initialSelections
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                search: this.searchQuery,
            }
        }

    },

    created() {
        this.request();
    },

    mounted() {
        this.$modal.show('item-selector');
    },

    watch: {

        parameters() {
            this.request();
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading('relationship-selector-listing', loading);
            }
        }

    },

    methods: {

        request() {
            this.loading = true;
            const url = cp_url(`relationship-fieldtype`);

            axios.get(url, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns.map(column => column.field);
                this.sortColumn = response.data.meta.sortColumn;
                this.items = response.data.data;
                this.meta = response.data.meta;
                this.loading = false;
                this.initializing = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        select() {
            this.$emit('selected', this.selections);
            this.close()
        },

        close() {
            this.$modal.hide('item-selector');
            this.$emit('closed');
        },

        selectionsUpdated(selections) {
            this.selections = selections;
        }

    }

}
</script>
