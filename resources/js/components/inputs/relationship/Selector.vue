<template>

    <modal name="item-selector" width="90%" height="90%" @closed="close">
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
                    :max-selections="maxSelections"
                    @selections-updated="selectionsUpdated"
                >
                    <div slot-scope="{ filteredRows: rows }" class="flex flex-col h-full justify-start">
                        <div class="data-list-header">
                            <data-list-toggle-all v-if="!hasMaxSelections" />
                            <data-list-search v-model="searchQuery" />

                            <div>
                                <button
                                    type="button"
                                    class="btn"
                                    @click="isCreating = true"
                                    v-text="__('Create')" />

                                <popper
                                    v-if="isCreating"
                                    :force-show="isCreating"
                                    ref="popper"
                                    trigger="click"
                                    :append-to-body="true"
                                    boundaries-selector="body"
                                    :options="{ placement: 'bottom' }"
                                >
                                    <div class="popover w-96 h-96 p-0">
                                        <inline-create-form
                                            class="popover-inner"
                                            @created="itemCreated"
                                            @closed="stopCreating"
                                        />
                                    </div>

                                    <!-- Popper needs a clickable element, but we don't want one.
                                    We'll show it programatically.  -->
                                    <div slot="reference" />
                                </popper>
                            </div>
                        </div>
                        <div class="flex-1 overflow-scroll">
                            <data-table
                                :loading="loading"
                                :allow-bulk-actions="true"
                                :toggle-selection-on-row-click="true"
                                @sorted="sorted"
                            >
                                <template slot="cell-url" slot-scope="{ row: entry }">
                                    <span class="text-2xs">{{ entry.url }}</span>
                                </template>
                            </data-table>
                        </div>

                        <data-list-pagination
                            class="p-1 border-t shadow-lg"
                            :resource-meta="meta"
                            @page-selected="setPage" />
                    </div>
                </data-list>
            </div>

            <div class="p-2 border-t flex items-center justify-between bg-grey-lightest">
                <div class="text-sm text-grey-light"
                    v-text="hasMaxSelections
                        ? __n(':count/:max selected', selections, { max: maxSelections })
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
import Popper from 'vue-popperjs';
import InlineCreateForm from './InlineCreateForm.vue';

export default {

    components: {
        Popper,
        InlineCreateForm
    },

    props: {
        url: String,
        initialSelections: Array,
        initialSortColumn: String,
        initialSortDirection: String,
        initialColumns: Array,
        maxSelections: Number
    },

    data() {
        return {
            initializing: true,
            loading: true,
            items: [],
            meta: {},
            columns: this.initialColumns,
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            page: 1,
            searchQuery: '',
            selections: _.clone(this.initialSelections),
            isCreating: false
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                search: this.searchQuery,
                columns: this.columns,
            }
        },

        hasMaxSelections() {
            return (this.maxSelections === Infinity) ? false : Boolean(this.maxSelections);
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

            return axios.get(this.url, { params: this.parameters }).then(response => {
                // this.columns = response.data.meta.columns.map(column => column.field);
                // this.sortColumn = response.data.meta.sortColumn;
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

        setPage(page) {
            this.page = page;
        },

        select() {
            this.$emit('selected', this.selections);
            this.close()
        },

        close() {
            this.$emit('closed');
        },

        selectionsUpdated(selections) {
            this.selections = selections;
        },

        itemCreated(item) {
            this.request();
            this.selections.push(item.id);
            this.stopCreating();
        },

        stopCreating() {
            this.isCreating = false;
        }

    }

}
</script>
