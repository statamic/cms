<template>

    <div class="h-full bg-white">

        <div v-if="initializing" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

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
            <div slot-scope="{}" class="flex flex-col h-full">
                <div class="bg-white border-b flex items-center justify-between bg-grey-20">
                    <data-list-toggle-all v-if="!hasMaxSelections" />
                    <div class="p-2 flex flex-1 items-center">
                        <div class="flex-1">
                            <data-list-search
                                v-if="search"
                                v-model="searchQuery"
                                class="bg-transparent p-0" />
                        </div>

                        <button
                            v-if="canCreate"
                            type="button"
                            class="ml-2 btn"
                            @click="isCreating = true"
                            v-text="`${__('Create')}...`" />

                        <button
                            type="button"
                            class="btn btn-primary ml-2"
                            @click="select"
                            v-text="hasMaxSelections
                                ? __n('Select (:count/:max)', selections, { max: maxSelections })
                                : __n('Select (:count)', selections)" />

                        <button
                            type="button"
                            class="btn-close"
                            @click="close"
                            v-html="'&times'" />
                    </div>
                </div>

                <div class="flex-1 flex flex-col overflow-scroll">
                    <div class="flex flex-col h-full justify-start">
                        <div class="flex-1">
                            <data-list-table
                                :loading="loading"
                                :allow-bulk-actions="true"
                                :toggle-selection-on-row-click="true"
                                @sorted="sorted"
                                class="cursor-pointer"
                            >
                                <template slot="cell-title" slot-scope="{ row: entry }">
                                    <div class="flex items-center">
                                        <div v-if="entry.published !== undefined" class="little-dot mr-1" :class="[entry.published ? 'bg-green' : 'bg-grey-40']" />
                                        {{ entry.title }}
                                    </div>
                                </template>
                                <template slot="cell-url" slot-scope="{ row: entry }">
                                    <span class="text-2xs">{{ entry.url }}</span>
                                </template>
                            </data-list-table>
                        </div>

                        <data-list-pagination
                            class="p-1 border-t shadow-lg"
                            :resource-meta="meta"
                            @page-selected="setPage" />
                    </div>
                </div>

                <inline-create-form
                    v-if="isCreating"
                    :site="site"
                    @created="itemCreated"
                    @closed="stopCreating"
                />

            </div>
        </data-list>
    </div>

</template>

<script>
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
        maxSelections: Number,
        site: String,
        search: Boolean,
        canCreate: Boolean,
        exclusions: {
            type: Array,
            default: () => []
        }
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
            isCreating: false,
            requestOnParameterChange: true,
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                site: this.site,
                exclusions: this.exclusions,
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

        parameters(param, oldparam) {
            if (this.requestOnParameterChange) this.request();
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading('relationship-selector-listing', loading);
            }
        },

        searchQuery(query) {
            this.requestOnParameterChange = false;

            this.sortColumn = null;
            this.sortDirection = null;
            this.page = 1;

            this.request()
                .then(() => this.requestOnParameterChange = true);
        }

    },

    methods: {

        request() {
            this.loading = true;

            const params = {...this.parameters, ...{
                search: this.searchQuery,
            }};

            return this.$axios.get(this.url, { params }).then(response => {
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
