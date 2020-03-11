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
                    WIP
                </div>
            </popover>

            <data-list-search :value="searchQuery" />

            <!-- TODO: Use Filter.vue for pinned filters? -->

            <template v-if="isFiltering">
                <popover v-if="canSave" placement="bottom-end" ref="savePopover">
                    <template slot="trigger">
                        <button class="input-group-item px-1.5">{{ __('Save Filters') }}</button>
                    </template>
                    <div class="p-2 w-96">
                        <h6 v-text="__('Saved filter name')" class="mb-1" />
                        <div class="flex items-center">
                            <input class="input-text border-r rounded-r" type="text" v-model="presetName" @keydown.enter="save" ref="savedFilterName">
                            <button class="btn-primary ml-1" @click="save" :disabled="! presetName">Save</button>
                        </div>
                    </div>
                </popover>
                <button class="input-group-append px-1.5" @click="reset">{{ __('Reset') }}</button>
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
            presetName: null,
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
            return ! _.isEmpty(this.activeFilters) || this.searchQuery;
        },

        isDirty() {
            return true;
        },

        presetSlug() {
            return this.$slugify(this.presetName, '_');
        },

        preferencesKey() {
            if (! this.preferencesPrefix || ! this.presetName) return null;

            return `${this.preferencesPrefix}.filters.${this.presetSlug}`;
        },

        preferencesPayload() {
            if (! this.presetName) return null;

            let payload = {
                display: this.presetName
            };

            if (this.searchQuery) payload.query = this.searchQuery;
            if (this.activeCount) payload.filters = clone(this.activeFilters);

            return payload;
        },

        canSave() {
            return this.savesPresets && this.isDirty && this.preferencesPrefix;
        },

    },

    methods: {

        dismiss() {
            this.filtering = false
        },

        filterChanged(handle, values) {
            this.$emit('changed', { handle, values });
        },

        save() {
            if (! this.canSave || ! this.preferencesPayload) return;

            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.preferencesPayload)
                .then(response => {
                    this.saving = false;
                    this.$refs.savePopover.close();
                    this.$emit('saved', this.presetSlug);
                    this.$toast.success(__('Filter preset saved'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save filter preset'));
                });
        },

        reset() {
            this.$emit('reset');
            this.$events.$emit('search-query-changed', '');
        },

        // remove() {
        //     this.saving = true;

        //     this.$preferences.remove(this.preferencesKey)
        //         .then(response => {
        //             this.saving = false;
        //             this.$events.$emit('filters-reset');
        //             this.$toast.success(__('Filters reset'));
        //         })
        //         .catch(error => {
        //             this.saving = false;
        //             this.$toast.error(__('Unable to reset filters'));
        //         });
        // }

        pinnedComponent(slug) {
            return `data-list-filter-${slug}`;
        },

    }

}
</script>
