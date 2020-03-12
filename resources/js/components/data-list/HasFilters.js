export default {

    data() {
        return {
            activePreset: null,
            searchQuery: '',
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
        this.$on('filters-reset', this.filtersReset);
    },

    methods: {

        searchChanged(query) {
            this.searchQuery = query;
        },

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
            this.activePreset = null;
            this.searchQuery = '';
            this.activeFilters = {};
        },

        unselectAllItems() {
            if (this.$refs.toggleAll) {
                this.$refs.toggleAll.uncheckAllItems();
            }
        },

        selectPreset(handle, preset)  {
            this.activePreset = handle;
            this.searchQuery = preset.query;

            this.filtersChanged(preset.filters);
        },

    }

}
