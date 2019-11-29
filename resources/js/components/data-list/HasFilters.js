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
        this.$events.$on('filter-changed', this.filterChanged);
        this.$events.$on('filters-reset', this.filtersReset);
    },

    methods: {

        hasFields(values) {
            for (const fieldHandle in values) {
                if (values[fieldHandle]) return true;
            }
            return false;
        },

        filterChanged({ handle, values }, unselectAll = true) {
            if (values && this.hasFields(values)) {
                Vue.set(this.activeFilters, handle, values);
            } else {
                Vue.delete(this.activeFilters, handle);
            }
            if (unselectAll) this.unselectAllItems();
        },

        filtersChanged(filters) {
            for (const handle in filters) {
                const values = filters[handle];
                this.filterChanged({ handle, values }, false);
            }
            this.unselectAllItems();
        },

        filtersReset() {
            this.filters.forEach(filter => {
                Vue.set(this.activeFilters, filter.handle, filter.values);
            });
        },

        unselectAllItems() {
            if (this.$refs.toggleAll) {
                this.$refs.toggleAll.uncheckAllItems();
            }
        }

    }

}
