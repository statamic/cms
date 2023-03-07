<template>
    <div class="w-full" v-if="isFiltering || isSearching">
        <div class="flex flex-wrap px-3 border-b pt-2">

            <!-- Field filter (requires custom selection UI) -->
            <popover v-if="fieldFilter">
                <template slot="trigger">
                    <button class="filter-badge filter-badge-control mr-2 mb-2" @click="resetFilterPopover">
                        {{ __('Field') }}
                        <svg-icon name="chevron-down-xs" class="w-2 h-2 mx-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover, afterClosed: afterPopoverClosed }">
                    <div class="flex flex-col text-left w-64">
                        <h6 class="p-4 pb-0" v-text="__('Show everything where:')"/>
                        <div class="filter-fields text-sm">
                            <field-filter
                                ref="fieldFilter"
                                :config="fieldFilter"
                                :values="activeFilters.fields || {}"
                                :badges="fieldFilterBadges"
                                :popover-closed="afterPopoverClosed"
                                @changed="$emit('filter-changed', {handle: 'fields', values: $event})"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                            <data-list-filter
                                v-for="filter in standardFilters"
                                v-if="creating === filter.handle"
                                :key="filter.handle"
                                :filter="filter"
                                :values="activeFilters[filter.handle]"
                                @changed="$emit('filter-changed', {handle: filter.handle, values: $event})"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <!-- Standard non-field filters -->
            <popover v-if="standardFilters.length" v-for="filter in standardFilters" :key="filter.handle" placement="bottom-end">
                <template slot="trigger">
                    <button class="filter-badge filter-badge-control mr-2 mb-2">
                        {{ filter.title }}
                        <svg-icon name="chevron-down-xs" class="w-2 h-2 mx-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="filter-fields">
                        <data-list-filter
                            :key="filter.handle"
                            :filter="filter"
                            :values="activeFilters[filter.handle]"
                            @changed="$emit('filter-changed', {handle: filter.handle, values: $event})"
                            @closed="closePopover"
                        />
                    </div>
                </template>
            </popover>

            <!-- Active filter badges -->
            <div class="filter-badge mr-2 mb-2" v-for="(badge, handle) in fieldFilterBadges">
                <span>{{ badge }}</span>
                <button @click="removeFieldFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>
            <div class="filter-badge mr-2 mb-2" v-for="(badge, handle) in standardBadges">
                <span>{{ badge }}</span>
                <button @click="removeStandardFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>

        </div>
    </div>

</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilter from './FieldFilter.vue';

export default {

    components: {
        DataListFilter,
        FieldFilter,
    },

    props: {
        filters: {
            type: Array,
            default: () => [],
        },
        activePreset: String,
        activePresetPayload: Object,
        activeFilters: Object,
        activeFilterBadges: Object,
        activeCount: Number,
        searchQuery: String,
        savesPresets: Boolean,
        preferencesPrefix: String,
        isSearching: Boolean,
    },

    data() {
        return {
            filtering: false,
            creating: false,
            saving: false,
            deleting: false,
            savingPresetName: null,
            presets: [],
        }
    },

    inject: ['sharedState'],

    watch: {
        activePresetPayload: {
            deep: true,
            handler(preset) {
                this.savingPresetName = preset.display || null;
            }
        }
    },

    computed: {

        fieldFilter() {
            return this.filters.find(filter => filter.handle === 'fields');
        },

        standardFilters() {
            return this.filters.filter(filter => filter.handle !== 'fields');
        },

        fieldFilterBadges() {
            return data_get(this.activeFilterBadges, 'fields', {});
        },

        standardBadges() {
            return _.omit(this.activeFilterBadges, 'fields');
        },

        isFiltering() {
            return ! _.isEmpty(this.activeFilters) || this.searchQuery || this.activePreset;
        },

        isDirty() {
            if (! this.isFiltering) return false;

            if (this.activePreset) {
                return this.activePresetPayload.query != this.searchQuery
                    || ! _.isEqual(this.activePresetPayload.filters || {}, this.activeFilters);
            }

            return true;
        },

        canSave() {
            return this.savesPresets && this.isDirty && this.preferencesPrefix;
        },

        savingPresetHandle() {
            return this.$slugify(this.savingPresetName, '_');
        },

        isUpdatingPreset() {
            return this.savingPresetHandle === this.activePreset;
        },

        preferencesKey() {
            let handle = this.savingPresetHandle || this.activePreset;

            if (! this.preferencesPrefix || ! handle) return null;

            return `${this.preferencesPrefix}.filters.${handle}`;
        },

        preferencesPayload() {
            if (! this.savingPresetName) return null;

            let payload = {
                display: this.savingPresetName
            };

            if (this.searchQuery) payload.query = this.searchQuery;
            if (this.activeCount) payload.filters = clone(this.activeFilters);

            return payload;
        },

    },

    methods: {

        resetFilterPopover() {
            this.creating = false;

            this.$refs.fieldFilter.resetInitialValues();
        },

        removeFieldFilter(handle) {
            let fields = clone(this.activeFilters.fields);

            delete fields[handle];

            this.$emit('filter-changed', {handle: 'fields', values: fields});
        },

        removeStandardFilter(handle) {
            this.$emit('filter-changed', {handle: handle, values: null});
        },

        save() {
            if (! this.canSave || ! this.preferencesPayload) return;

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.preferencesPayload)
                .then(response => {
                    this.$refs.savePopover.close();
                    this.$emit('saved', this.savingPresetHandle);
                    this.$toast.success(this.isUpdatingPreset ? __('Filter preset updated') : __('Filter preset saved'));
                    this.savingPresetName = null;
                    this.saving = false;
                })
                .catch(error => {
                    this.$toast.error(this.isUpdatingPreset ? __('Unable to update filter preset') : __('Unable to save filter preset'));
                    this.saving = false;
                });
        },

        reset() {
            return this.activePreset
                ? this.$emit('restore-preset', this.activePreset)
                : this.$emit('reset');
        },

        remove() {
            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.$emit('deleted', this.activePreset);
                    this.$toast.success(__('Filter preset deleted'));
                    this.deleting = false;
                })
                .catch(error => {
                    this.$toast.error(__('Unable to delete filter preset'));
                    this.deleting = false;
                });
        },

    }

}
</script>
