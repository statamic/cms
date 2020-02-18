<template>
    <div class="w-full">
        <div class="input-group">
            <popper
                trigger="click"
                :options="{
                    placement: 'bottom-end',
                    modifiers: { offset: { offset: '0,10px' } }
                }">

                <div class="bg-white flex flex-col shadow-popover block p-2 rounded-md text-left z-max">
                    <p class="text-xs mb-1">Show all entries where:</p>
                    <field-filters
                        v-if="fieldsFilter"
                        :config="fieldsFilter"
                        :filters="activeFilters['fields']"
                        @changed="filterChanged('fields', $event)"
                    />
                </div>
                <button class="input-group-prepend outline-none cursor-pointer px-2" slot="reference">
                    {{ __('Filter') }}
                    <svg height="8" width="8" viewBox="0 0 10 6.5" class="ml-sm"><path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor" /></svg>
                </button>
            </popper>

            <!-- <data-list-filter
                v-for="filter in standardFilters"
                :key="filter.handle"
                :filter="filter"
                :values="activeFilters[filter.handle]"
                @changed="filterChanged(filter.handle, $event)"
            /> -->

            <!-- <div v-if="preferencesKey">
                <button class="btn mr-2" @click="reset">{{ __('Reset All') }}</button>
                <button class="btn-primary" @click="save">{{ __('Save Filters') }}</button>
            </div> -->

            <!-- @TODO Search isn't wired up correctly. -->
            <data-list-search v-model="sharedState.searchQuery" />

            <button class="input-group-append px-1.5" v-if="sharedState.searchQuery">
                {{ __('Save Search') }}
            </button>

            <!-- @TODO: Need to create actual child components for these native "pinned" filters.
                We'll need date, status, and author to ship with, plus any custom "promoted" filters. -->
            <popper trigger="click" :options="{ placement: 'bottom-end', modifiers: { offset: { offset: '0,10px' } }}">
                <div class="bg-white flex flex-col shadow-popover block px-2 py-1 rounded-md text-left z-max">
                    <div class="mb-1">
                        <label for="published" class="mb-sm">
                            <input type="checkbox" class="mr-sm" name="published" id="published"> Published
                        </label>
                        <label for="scheduled" class="mb-sm">
                            <input type="checkbox" class="mr-sm" name="scheduled" id="scheduled"> Scheduled
                        </label>
                        <label for="draft" class="mb-sm">
                            <input type="checkbox" class="mr-sm" name="draft" id="draft"> Draft
                        </label>
                    </div>
                    <a class="text-grey-60 hover:text-grey-90 text-sm">Clear</a>
                </div>
                <button class="input-group-append px-1.5" slot="reference">
                    {{ __('Status') }}
                    <svg height="8" width="8" viewBox="0 0 10 6.5" class="ml-sm"><path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor" /></svg>
                </button>
            </popper>

            <!-- Saving filters stores a single, default filter state.
            This should create multiple filter states you can pick from. -->
            <button class="input-group-append rounded-l-0 px-1.5" v-if="activeFilters.fields" @click="save">
                {{ __('Save filters') }}
            </button>
            <button class="input-group-append rounded-l-0 px-1.5" v-if="activeFilters.fields" @click="reset">
                {{ __('Reset') }}
            </button>
        </div>

        <div class="flex flex-wrap mt-1" v-if="activeFilters.fields">
            <div class="filter-badge mr-1" v-for="(filter, field) in activeFilters.fields">
                <!-- @TODO: Need a way to control the grammar in a nice way. For example,
                it would read better to say 'Field Name is value' instead of 'field_name = "value"' -->
                <span>
                    {{ field }} {{ filter.operator }} "{{ filter.value }}"
                </span>
                <!-- @TODO: Need a @click="deleteFilter" here -->
                <button>&times;</button>
            </div>
        </div>
    </div>

</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilters from './FieldFilters.vue';
import Popper from 'vue-popperjs';

export default {

    components: {
        DataListFilter,
        FieldFilters,
        Popper
    },

    props: {
        filters: Array,
        activeFilters: Object,
        activeCount: Number,
        preferencesKey: String
    },

    data() {
        return {
            filtering: false,
            saving: false, // dummy var to stub out Add Filter button
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

        preferencesPayload() {
            return this.activeCount ? clone(this.activeFilters) : {};
        }

    },

    methods: {

        dismiss() {
            this.filtering = false
        },

        filterChanged(handle, values) {
            this.$events.$emit('filter-changed', { handle, values });
        },

        save() {
            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.preferencesPayload)
                .then(response => {
                    this.saving = false;
                    this.$toast.success(__('Filters saved'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Unable to save filters'));
                });
        },

        reset() {
            this.saving = true;

            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.saving = false;
                    this.$events.$emit('filters-reset');
                    this.$toast.success(__('Filters reset'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Unable to reset filters'));
                });
        }
    }

}
</script>
