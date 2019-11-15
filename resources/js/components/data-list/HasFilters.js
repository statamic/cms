export default {

    data() {
        return {
            activeFilters: {},
        }
    },

    computed: {

        activeFilterCount() {
            let count = Object.keys(this.activeFilters).length;

            if (this.activeFilters.hasOwnProperty('fields')) {
                count = count + Object.keys(this.activeFilters.fields).length - 1;
            }

            return count;
        },

        hasActiveFilters() {
            return this.activeFilterCount > 0;
        }

    },

    created() {
        this.$events.$on('filters-changed', this.filtersChanged);
        this.$events.$on('filters-reset', this.filtersReset);
    },

    methods: {

        filtersChanged(filters) {
            this.activeFilters = filters || {};
            this.unselectAllItems();
        },

        filtersReset() {
            let activeFilters = {};

            this.filters.forEach(filter => {
                activeFilters[filter.handle] = filter.values;
            });

            this.activeFilters = activeFilters;
        },

        unselectAllItems() {
            if (this.$refs.toggleAll) {
                this.$refs.toggleAll.uncheckAllItems();
            }
        }

    }

}
