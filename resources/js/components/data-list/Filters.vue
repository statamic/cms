<template>
    <div class="w-full">
        <div class="input-group focus-within-only">

            <popover v-if="filters.length">
                <template slot="trigger">
                    <button class="input-group-prepend cursor-pointer px-2" @click="resetFilterPopover">
                        {{ __('Filter') }}
                        <svg height="8" width="8" viewBox="0 0 10 6.5" class="ml-sm"><path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor" /></svg>
                    </button>
                </template>
                <template #default="{ close: closePopover, afterClosed: afterPopoverClosed }">
                    <div class="flex flex-col text-left w-64">
                        <h6 class="p-2 pb-0" v-text="__('Show everything where:')"/>
                        <div v-if="showFilterSelection" class="p-2 pt-1">
                            <button
                                v-for="filter in unpinnedFilters"
                                :key="filter.handle"
                                v-text="filter.title"
                                class="btn w-full mt-1"
                                @click="creating = filter.handle"
                            />
                        </div>
                        <div class="filter-fields text-sm">
                            <field-filter
                                v-show="showFieldFilter"
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

            <data-list-search ref="search" :value="searchQuery" @input="$emit('search-changed', $event)" />

            <template v-if="isFiltering">
                <popover v-if="canSave" placement="bottom-end" ref="savePopover">
                    <template slot="trigger">
                        <button class="input-group-item px-1.5">{{ __('Save') }}</button>
                    </template>
                    <div class="p-2 w-96">
                        <h6 v-text="__('Filter preset name')" class="mb-1" />
                        <div class="flex items-center">
                            <input class="input-text border-r rounded-r" type="text" v-model="savingPresetName" @keydown.enter="save" ref="savedFilterName">
                            <button class="btn-primary ml-1" @click="save" :disabled="saving || ! savingPresetName">Save</button>
                        </div>
                    </div>
                </popover>
                <button v-if="isDirty" class="input-group-item px-1.5" @click="reset">{{ __('Reset') }}</button>
                <button v-if="activePreset" class="flex items-center input-group-item px-1.5" @click="deleting = true"><svg-icon name="trash" class="w-4 h-4" /></button>
                <confirmation-modal
                    v-if="deleting"
                    :title="__('Delete Preset')"
                    :bodyText="__('Are you sure you want to delete this preset?')"
                    :buttonText="__('Delete')"
                    :danger="true"
                    @confirm="remove"
                    @cancel="deleting = false"
                />
            </template>

            <popover v-if="pinnedFilters.length" v-for="filter in pinnedFilters" :key="filter.handle" placement="bottom-end">
                <template slot="trigger">
                    <button class="input-group-item px-1.5">
                        {{ filter.title }}
                        <svg height="8" width="8" viewBox="0 0 10 6.5" class="ml-sm"><path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor" /></svg>
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

        </div>

        <div class="flex flex-wrap mt-1" v-if="activeCount">
            <div class="filter-badge mr-1" v-for="(badge, handle) in fieldFilterBadges">
                <span>{{ badge }}</span>
                <button @click="removeFieldFilter(handle)">&times;</button>
            </div>
            <div class="filter-badge mr-1" v-for="(badge, handle) in standardBadges">
                <span>{{ badge }}</span>
                <button @click="removeStandardFilter(handle)">&times;</button>
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

        pinnedFilters() {
            return this.filters.filter(filter => filter.pinned);
        },

        unpinnedFilters() {
            return this.filters.filter(filter => ! filter.pinned);
        },

        showFilterSelection() {
            if (this.fieldFilter && this.unpinnedFilters.length === 1) return false;

            return ! this.creating;
        },

        showFieldFilter() {
            if (this.fieldFilter && this.unpinnedFilters.length === 1) return true;

            return this.creating === 'fields';
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
