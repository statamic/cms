<template>
    <div>
        <button class="btn btn-flat btn-icon-only dropdown-toggle relative" @click="filtering = !filtering">
            <svg-icon name="filter-text" class="w-4 h-4 mr-1" />
            <span>{{ __('Filters') }}</span>
            <div v-if="activeCount" class="badge ml-1 bg-grey-40" v-text="activeCount" />
        </button>
        <stack half name="filters" v-if="filtering" @closed="dismiss">
            <div class="h-full overflow-auto bg-white">

                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                    {{ __('Filters') }}
                    <button
                        type="button"
                        class="btn-close"
                        @click="dismiss"
                        v-html="'&times'" />
                </div>

                <data-list-filter
                    v-for="filter in standardFilters"
                    :key="filter.handle"
                    :filter="filter"
                    :initial-values="activeFilters[filter.handle]"
                    @changed="filterChanged(filter.handle, $event)"
                />

                <field-filters
                    v-if="fieldsFilter"
                    :filter="fieldsFilter"
                    :initial-filters="activeFilters['fields']"
                    @changed="filterChanged('fields', $event)"
                />

                <div v-if="preferencesKey" class="p-3 border-t">
                    <div class="flex">
                        <button class="btn mr-2" @click="reset">{{ __('Reset All') }}</button>
                        <button class="btn-primary" @click="save">{{ __('Save Filters') }}</button>
                        <loading-graphic v-if="saving" class="ml-1" :inline="true" :text="__('Saving')" />
                    </div>
                </div>

            </div>
        </stack>
    </div>
</template>

<script>
import DataListFilter from './Filter.vue';
import FieldFilters from './FieldFilters.vue';

export default {

    components: {
        DataListFilter,
        FieldFilters,
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

    computed: {

        standardFilters() {
            return this.filters.filter(filter => filter.handle !== 'fields');
        },

        fieldsFilter() {
            return this.filters.filter(filter => filter.handle === 'fields')[0];
        },

        preferencesPayload() {
            return this.activeCount ? clone(this.activeFilters) : {};
        }

    },

    methods: {

        dismiss() {
            this.filtering = false
        },

        filterChanged(handle, value) {
            let filters = this.activeFilters;
            if (value && ! _.isEmpty(value)) {
                Vue.set(filters, handle, value);
            } else {
                Vue.delete(filters, handle);
            }
            this.$events.$emit('filters-changed', filters);
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
                    this.$toast.error(__('Something went wrong'));
                });
        },

        reset() {
            this.saving = true;

            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.saving = false;
                    this.$toast.success(__('Filters reset'));
                    this.$events.$emit('filters-reset');
                })
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(__('Something went wrong'));
                });
        }
    },

    created() {
        this.$mousetrap.bind('esc', this.dismiss)
    },

}
</script>
