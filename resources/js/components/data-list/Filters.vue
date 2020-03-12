<template>
    <div class="w-full">
        <div class="input-group">
            <popover>
                <template slot="trigger">
                    <button class="input-group-prepend outline-none cursor-pointer px-2">
                        {{ __('Filter') }}
                        <svg height="8" width="8" viewBox="0 0 10 6.5" class="ml-sm"><path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor" /></svg>
                    </button>
                </template>
                <div class="flex flex-col p-2 text-left w-64">
                    <field-filters
                        v-if="fieldsFilter"
                        :config="fieldsFilter"
                        :filters="activeFilters['fields']"
                        @changed="$emit('filter-changed', $event)"
                    />
                </div>
            </popover>

            <data-list-search :value="searchQuery" @input="$emit('search-changed', $event)" />

            <!-- TODO: Use Filter.vue for pinned filters? -->

            <template v-if="isFiltering">
                <popover v-if="canSave" placement="bottom-end" ref="savePopover">
                    <template slot="trigger">
                        <button class="input-group-item px-1.5">{{ __('Save') }}</button>
                    </template>
                    <div class="p-2 w-96">
                        <h6 v-text="__('Saved filter name')" class="mb-1" />
                        <div class="flex items-center">
                            <input class="input-text border-r rounded-r" type="text" v-model="newPresetName" @keydown.enter="save" ref="savedFilterName">
                            <button class="btn-primary ml-1" @click="save" :disabled="! newPresetName">Save</button>
                        </div>
                    </div>
                </popover>
                <button v-if="isDirty" class="input-group-append px-1.5" @click="reset">{{ __('Reset') }}</button>
                <button v-if="activePreset" class="input-group-append px-1.5" @click="deleting = true"><svg-icon name="trash" /></button>
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
        </div>

        <div class="flex flex-wrap mt-1" v-if="activeFilters.fields">
            <div class="filter-badge mr-1" v-for="(filter, field) in activeFilters.fields">
                <!-- @TODO: Need a way to control the grammar in a nice way. For example,
                it would read better to say 'Field Name is value' instead of 'field_name = "value"' -->
                <span>
                    {{ field }} {{ filter.operator }} "{{ filter.value }}"
                </span>
                <button @click="removeFilter(field)">&times;</button>
            </div>
        </div>
    </div>

</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilters from './FieldFilters.vue';

export default {
    components: {

        DataListFilter,
        FieldFilters
    },

    props: {
        filters: Array,
        activePreset: String,
        activeFilters: Object,
        activeCount: Number,
        searchQuery: String,
        savesPresets: Boolean,
        preferencesPrefix: String,
    },

    data() {
        return {
            filtering: false,
            saving: false, // dummy var to stub out Add Filter button
            deleting: false,
            newPresetName: null,
            presets: [],
        }
    },

    inject: ['sharedState'],

    computed: {

        standardFilters() {
            return this.filters.filter(filter => filter.handle !== 'fields');
        },

        fieldsFilter() {
            return this.filters.find(filter => filter.handle === 'fields');
        },

        isFiltering() {
            return ! _.isEmpty(this.activeFilters) || this.searchQuery || this.activePreset;
        },

        isDirty() {
            return true;
        },

        canSave() {
            return this.savesPresets && this.isDirty && this.preferencesPrefix;
        },

        newPresetHandle() {
            return this.$slugify(this.newPresetName, '_');
        },

        preferencesKey() {
            let handle = this.newPresetHandle || this.activePreset;

            if (! this.preferencesPrefix || ! handle) return null;

            return `${this.preferencesPrefix}.filters.${handle}`;
        },

        preferencesPayload() {
            if (! this.newPresetName) return null;

            let payload = {
                display: this.newPresetName
            };

            if (this.searchQuery) payload.query = this.searchQuery;
            if (this.activeCount) payload.filters = clone(this.activeFilters);

            return payload;
        },

    },

    methods: {

        dismiss() {
            this.filtering = false
        },

        save() {
            if (! this.canSave || ! this.preferencesPayload) return;

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.preferencesPayload)
                .then(response => {
                    this.$refs.savePopover.close();
                    this.$emit('saved', this.newPresetHandle);
                    this.$toast.success(__('Filter preset saved'));
                    this.newPresetName = null;
                    this.saving = false;
                })
                .catch(error => {
                    this.$toast.error(__('Unable to save filter preset'));
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
                    this.deleting = false;
                    this.$emit('deleted', this.activePreset);
                    this.$toast.success(__('Filter preset deleted'));
                })
                .catch(error => {
                    this.deleting = false;
                    this.$toast.error(__('Unable to delete filter preset'));
                });
        },

    }

}
</script>
