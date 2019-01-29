<template>
    <dropdown-list>
        <button class="btn btn-icon-only antialiased ml-2 dropdown-toggle relative" slot="trigger">
            <svg-icon name="filter" class="h-4 w-4 mr-1 text-current"></svg-icon>
            <span>{{ __('Filter') }}</span>
            <div v-if="activeFilterCount" class="badge ml-1 bg-blue" v-text="activeFilterCount" />
        </button>
        <ul class="dropdown-menu">
            <li><h6>{{ __('Filter List') }}</h6></li>
            <li class="divider"></li>

            <data-list-filter
                v-for="filter in filters"
                :key="filter.handle"
                :filter="filter"
                :initial-value="activeFilters[filter.handle]"
                @changed="filterChanged(filter.handle, $event)"
            />

            <li class="flex items-center">
                <select class="w-auto" :value="perPage" @change="$emit('per-page-changed', parseInt($event.target.value))">
                    <option
                        v-for="value in perPageOptions"
                        :key="value"
                        :value="value"
                        v-text="value" />
                </select>
                <span class='ml-1 text-2xs font-medium' v-text="__('Per Page')" />
            </li>
        </ul>
    </dropdown-list>
</template>

<script>
import DataListFilter from './Filter.vue';

export default {

    components: {
        DataListFilter
    },

    props: {
        perPage: Number,
        filters: Array,
        activeFilters: Object
    },

    data() {
        return {
            perPageOptions: [2, 25, 50, 100]
        }
    },

    computed: {

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
