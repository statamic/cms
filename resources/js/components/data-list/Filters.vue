<template>
    <div class="bg-gray-300 shadow-inner dark:bg-dark-600">
        <div class="flex flex-wrap items-center border-b px-3 pt-2 dark:border-dark-900">
            <!-- Field filter (requires custom selection UI) -->
            <popover v-if="fieldFilter" placement="bottom-start" @closed="fieldFilterClosed">
                <template #trigger>
                    <button
                        class="filter-badge filter-badge-control mb-2 ltr:mr-2 rtl:ml-2"
                        @click="resetFilterPopover"
                    >
                        {{ fieldFilter.title }}
                        <svg-icon name="micro/chevron-down-xs" class="mx-2 h-2 w-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="flex min-w-[18rem] flex-col ltr:text-left rtl:text-right">
                        <div class="filter-fields text-sm">
                            <field-filter
                                ref="fieldFilter"
                                :config="fieldFilter"
                                :values="activeFilters.fields || {}"
                                :badges="fieldFilterBadges"
                                @changed="$emit('changed', { handle: 'fields', values: $event })"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <!-- Standard pinned filters -->
            <popover
                v-if="pinnedFilters.length"
                v-for="filter in pinnedFilters"
                :key="filter.handle"
                placement="bottom-start"
                :stop-propagation="false"
            >
                <template #trigger>
                    <button class="filter-badge filter-badge-control mb-2 ltr:mr-2 rtl:ml-2">
                        {{ filter.title }}
                        <svg-icon name="micro/chevron-down-xs" class="mx-2 h-2 w-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="filter-fields w-64">
                        <data-list-filter
                            :key="filter.handle"
                            :filter="filter"
                            :values="activeFilters[filter.handle]"
                            @changed="$emit('changed', { handle: filter.handle, values: $event })"
                            @closed="closePopover"
                        />
                    </div>
                </template>
            </popover>

            <!-- Standard unpinned filters -->
            <popover v-if="unpinnedFilters.length" placement="bottom-start" :stop-propagation="false">
                <template #trigger>
                    <button
                        class="filter-badge filter-badge-control mb-2 ltr:mr-2 rtl:ml-2"
                        @click="resetFilterPopover"
                    >
                        {{ __('Filter') }}
                        <svg-icon name="micro/chevron-down-xs" class="mx-2 h-2 w-2" />
                    </button>
                </template>
                <template #default="{ close: closePopover }">
                    <div class="filter-fields w-64">
                        <h6 v-text="creatingFilterHeader" class="p-3 pb-0" />
                        <div v-if="showUnpinnedFilterSelection" class="p-3 pt-1">
                            <button
                                v-for="filter in unpinnedFilters"
                                :key="filter.handle"
                                v-text="filter.title"
                                class="btn mt-1 w-full"
                                @click="creating = filter.handle"
                            />
                        </div>
                        <div v-else>
                            <data-list-filter
                                v-for="filter in unpinnedFilters"
                                v-if="creating === filter.handle"
                                :key="filter.handle"
                                :filter="filter"
                                :values="activeFilters[filter.handle]"
                                @changed="$emit('changed', { handle: filter.handle, values: $event })"
                                @cleared="creating = false"
                                @closed="closePopover"
                            />
                        </div>
                    </div>
                </template>
            </popover>

            <!-- Active filter badges -->
            <div class="filter-badge mb-2 ltr:mr-2 rtl:ml-2" v-for="(badge, handle) in fieldFilterBadges">
                <span>{{ badge }}</span>
                <button @click="removeFieldFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>
            <div class="filter-badge mb-2 ltr:mr-2 rtl:ml-2" v-for="(badge, handle) in standardBadges">
                <span>{{ badge }}</span>
                <button @click="removeStandardFilter(handle)" v-tooltip="__('Remove Filter')">&times;</button>
            </div>
        </div>
    </div>
</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilter from './FieldFilter.vue';
import { isEqual } from 'lodash-es';

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
        };
    },

    inject: ['sharedState'],

    watch: {
        activePresetPayload: {
            deep: true,
            handler(preset) {
                this.savingPresetName = preset.display || null;
            },
        },
    },

    computed: {
        fieldFilter() {
            return this.filters.find((filter) => filter.handle === 'fields');
        },

        standardFilters() {
            return this.filters.filter((filter) => filter.handle !== 'fields');
        },

        pinnedFilters() {
            return this.standardFilters.filter((filter) => filter.pinned);
        },

        unpinnedFilters() {
            return this.standardFilters.filter((filter) => !filter.pinned);
        },

        creatingFilter() {
            return this.unpinnedFilters.find((filter) => filter.handle === this.creating);
        },

        creatingFilterHeader() {
            let text = data_get(this.creatingFilter, 'title', 'Filter where');

            return __(text) + ':';
        },

        showUnpinnedFilterSelection() {
            return !this.creating;
        },

        fieldFilterBadges() {
            return data_get(this.activeFilterBadges, 'fields', {});
        },

        standardBadges() {
            const { fields, ...badges } = this.activeFilterBadges;
            return badges;
        },

        isFiltering() {
            return Object.keys(this.activeFilters).length || this.searchQuery || this.activePreset;
        },

        isDirty() {
            if (!this.isFiltering) return false;

            if (this.activePreset) {
                return (
                    this.activePresetPayload.query != this.searchQuery ||
                    !isEqual(this.activePresetPayload.filters || {}, this.activeFilters)
                );
            }

            return true;
        },

        canSave() {
            return this.savesPresets && this.isDirty && this.preferencesPrefix;
        },

        savingPresetHandle() {
            return snake_case(this.savingPresetName);
        },

        isUpdatingPreset() {
            return this.savingPresetHandle === this.activePreset;
        },

        preferencesKey() {
            let handle = this.savingPresetHandle || this.activePreset;

            if (!this.preferencesPrefix || !handle) return null;

            return `${this.preferencesPrefix}.filters.${handle}`;
        },

        preferencesPayload() {
            if (!this.savingPresetName) return null;

            let payload = {
                display: this.savingPresetName,
            };

            if (this.searchQuery) payload.query = this.searchQuery;
            if (this.activeCount) payload.filters = clone(this.activeFilters);

            return payload;
        },
    },

    methods: {
        resetFilterPopover() {
            this.creating = false;

            setTimeout(() => this.$refs.fieldFilter?.resetInitialValues(), 100); // wait for popover to appear
        },

        fieldFilterClosed() {
            this.$refs.fieldFilter.popoverClosed();
        },

        removeFieldFilter(handle) {
            let fields = clone(this.activeFilters.fields);

            delete fields[handle];

            this.$emit('changed', { handle: 'fields', values: fields });
        },

        removeStandardFilter(handle) {
            this.$emit('changed', { handle: handle, values: null });
        },

        save() {
            if (!this.canSave || !this.preferencesPayload) return;

            this.saving = true;

            this.$preferences
                .set(this.preferencesKey, this.preferencesPayload)
                .then((response) => {
                    this.$refs.savePopover.close();
                    this.$emit('saved', this.savingPresetHandle);
                    this.$toast.success(
                        this.isUpdatingPreset ? __('Filter preset updated') : __('Filter preset saved'),
                    );
                    this.savingPresetName = null;
                    this.saving = false;
                })
                .catch((error) => {
                    this.$toast.error(
                        this.isUpdatingPreset
                            ? __('Unable to update filter preset')
                            : __('Unable to save filter preset'),
                    );
                    this.saving = false;
                });
        },

        remove() {
            this.$preferences
                .remove(this.preferencesKey)
                .then((response) => {
                    this.$emit('deleted', this.activePreset);
                    this.$toast.success(__('Filter preset deleted'));
                    this.deleting = false;
                })
                .catch((error) => {
                    this.$toast.error(__('Unable to delete filter preset'));
                    this.deleting = false;
                });
        },
    },
};
</script>
