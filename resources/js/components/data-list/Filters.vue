<template>
    <dropdown-list>
        <button class="btn btn-icon-only antialiased ml-2 dropdown-toggle relative" slot="trigger">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.8 2.1A1 1 0 0 0 21 .5H2.981a1 1 0 0 0-.8 1.6l7.808 10.491V22.5a1 1 0 0 0 1.6.8l2-1.5a1 1 0 0 0 .4-.8v-8.41z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div v-if="activeFilterCount" class="badge ml-1 bg-blue" v-text="activeFilterCount" />
        </button>
        <ul class="dropdown-menu">
            <data-list-filter
                v-for="filter in filters"
                :key="filter.handle"
                :filter="filter"
                :initial-value="activeFilters[filter.handle]"
                @changed="filterChanged(filter.handle, $event)"
            />

            <li>
                <h6>Per Page</h6>
                <select class="w-full" :value="perPage" @change="$emit('per-page-changed', parseInt($event.target.value))">
                    <option
                        v-for="value in perPageOptions"
                        :key="value"
                        :value="value"
                        v-text="value" />
                </select>
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
