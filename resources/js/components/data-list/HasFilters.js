export default {

    data() {
        return {
            activePreset: null,
            activePresetPayload: {},
            searchQuery: '',
            activeFilters: {},
            activeFilterBadges: {},
        }
    },

    computed: {

        activeFilterCount() {
            let count = Object.keys(this.activeFilters).length;

            if (this.activeFilters.hasOwnProperty('fields')) {
                count = count + Object.keys(this.activeFilters.fields).filter(field => field != 'badge').length - 1;
            }

            return count;
        },

        hasActiveFilters() {
            return this.activeFilterCount > 0;
        }

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
            this.activeFilters = {};

            for (const handle in filters) {
                const values = filters[handle];
                this.filterChanged({ handle, values }, false);
            }

            this.unselectAllItems();
        },

        filtersReset() {
            this.activePreset = null;
            this.activePresetPayload = {};
            this.searchQuery = '';
            this.activeFilters = {};
            this.activeFilterBadges = {};
        },

        unselectAllItems() {
            if (this.$refs.dataList) {
                this.$refs.dataList.clearSelections();
            }
        },

        selectPreset(handle, preset)  {
            this.activePreset = handle;
            this.activePresetPayload = preset;
            this.searchQuery = preset.query;

            this.filtersChanged(preset.filters);
        },

        autoApplyFilters(filters) {
            if (! filters) return;

            let values = {};

            filters.filter(filter => ! _.isEmpty(filter.auto_apply)).forEach(filter => {
                values[filter.handle] = filter.auto_apply;
            });

            this.activeFilters = values;
        },

    }

}
