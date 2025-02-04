export default {

    data() {
        return {
            activeFilterBadges: {},
            activeFilters: {},
            activePreset: null,
            activePresetPayload: {},
            searchQuery: '',
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

        canSave() {
            return this.isDirty && this.preferencesPrefix;
        },

        isDirty() {
            if (! this.isFiltering) return false;

            if (this.activePreset) {
                return this.activePresetPayload.query != this.searchQuery
                    || ! _.isEqual(this.activePresetPayload.filters || {}, this.activeFilters);
            }

            return true;
        },

        isFiltering() {
            return ! _.isEmpty(this.activeFilters) || this.searchQuery || this.activePreset;
        },

        hasActiveFilters() {
            return this.activeFilterCount > 0;
        },

        searchPlaceholder() {
            if (this.activePreset) {
                return `${__('Searching in:')} ${this.activePresetPayload.display}`;
            }

            return __('Search');
        },

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
                this.activeFilters[handle] = values;
            } else {
                delete this.activeFilters[handle];
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
