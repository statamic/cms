<template>
    <div>
        <button class="btn btn-flat btn-icon-only ml-2 dropdown-toggle relative" @click="filtering = true">
            <svg-icon name="filter-text" class="w-4 h-4 mr-1" />
            <span>{{ __('Filters') }}</span>
            <div v-if="activeFilterCount" class="badge ml-1" v-text="activeFilterCount" />
        </button>
        <stack name="filters" v-if="filtering" @closed="filtering = false">
            <div slot-scope="{ close }" class="h-full bg-white p-3">

                <div class="pb-3 text-lg font-medium flex items-center justify-between">
                    {{ __('Filters') }}
                    <button
                        type="button"
                        class="ml-2 p-1 text-xl text-grey-60"
                        @click="close"
                        v-html="'&times'" />
                </div>

                <data-list-filter
                    v-for="filter in standardFilters"
                    :key="filter.handle"
                    :filter="filter"
                    :initial-value="activeFilters[filter.handle]"
                    @changed="filterChanged(filter.handle, $event)"
                />

                <field-filters
                    :filter="fieldsFilter"
                    :initial-value="activeFilters['fields']"
                    @changed="filterChanged('fields', $event)"
                />

                <select class="w-auto mt-3" :value="perPage" @change="$emit('per-page-changed', parseInt($event.target.value))">
                    <option
                        v-for="value in perPageOptions"
                        :key="value"
                        :value="value"
                        v-text="value" />
                </select>
                <span class='ml-1 text-2xs font-medium' v-text="__('Per Page')" />

            </div>
        </stack>
    </div>
</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilters from './FieldFilters.vue';

export default {

    components: {
        DataListFilter,
        FieldFilters,
    },

    props: {
        perPage: Number,
        filters: Array,
        activeFilters: Object
    },

    data() {
        return {
            filtering: false,
            perPageOptions: [2, 25, 50, 100]
        }
    },

    computed: {

        standardFilters() {
            return this.filters.filter(filter => filter.handle !== 'fields');
        },

        fieldsFilter() {
            return this.filters.filter(filter => filter.handle === 'fields')[0];
        },

        activeFilterCount() {
            return Object.keys(this.activeFilters).length;
        }

    },

    methods: {

        filterChanged(handle, value) {
            let filters = this.activeFilters;
            if (value) {
                Vue.set(filters, handle, value);
            } else {
                Vue.delete(filters, handle);
            }
            this.$emit('filters-changed', filters);
        }

    }

}
</script>
