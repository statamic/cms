<template>
    <div>
        <button class="btn btn-flat btn-icon-only ml-2 dropdown-toggle relative" @click="filtering = !filtering">
            <svg-icon name="filter-text" class="w-4 h-4 mr-1" />
            <span>{{ __('Filters') }}</span>
            <div v-if="activeFilterCount" class="badge ml-1 bg-grey-40" v-text="activeFilterCount" />
        </button>
        <pane name="filters" v-if="filtering">
            <div>

                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                    {{ __('Filters') }}
                    <button
                        type="button"
                        class="ml-2 p-1 text-xl text-grey-60 hover:text-grey-80"
                        @click="filtering = false"
                        v-html="'&times'" />
                </div>

                <data-list-filter
                    v-for="filter in standardFilters"
                    :key="filter.handle"
                    :filter="filter"
                    :initial-value="activeFilters[filter.handle]"
                    @changed="filterChanged(filter.handle, $event)"
                />

                <field-filters
                    :filter="fieldsFilter"
                    :initial-value="activeFilters['fields']"
                    @changed="filterChanged('fields', $event)"
                />

                <div class="p-3 pt-0">
                    <select class="w-auto mt-3" :value="perPage" @change="$emit('per-page-changed', parseInt($event.target.value))">
                        <option
                            v-for="value in perPageOptions"
                            :key="value"
                            :value="value"
                            v-text="value" />
                    </select>
                    <span class='ml-1 text-2xs font-medium' v-text="__('Per Page')" />
                </div>

                <div v-if="preferencesKey" class="p-3 pt-0">
                    <loading-graphic v-if="saving" :inline="true" :text="__('Saving')" />
                    <template v-else>
                        <div class="flex justify-center mt-3">
                            <button class="btn-flat w-full block btn-sm" @click="save">{{ __('Save') }}</button>
                        </div>
                        <div class="flex justify-center mt-2">
                            <button class="btn-flat w-full block btn-sm" @click="reset">{{ __('Reset') }}</button>
                        </div>
                    </template>
                </div>

            </div>
        </pane>
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
        perPage: Number,
        filters: Array,
        activeFilters: Object,
        preferencesKey: String
    },

    data() {
        return {
            filtering: false,
            perPageOptions: [2, 25, 50, 100],
            saving: false,
        }
    },

    computed: {

        standardFilters() {
            return this.filters.filter(filter => filter.handle !== 'fields');
        },

        fieldsFilter() {
            return this.filters.filter(filter => filter.handle === 'fields')[0];
        },

        activeFilterCount() {
            let count = Object.keys(this.activeFilters).length;

            if (this.activeFilters.hasOwnProperty('fields')) {
                count = count + Object.keys(this.activeFilters.fields).length - 1;
            }

            return count;
        },

        preferencesPayload() {
            return {
                ...(this.activeFilterCount ? clone(this.activeFilters) : {}),
                perPage: this.perPage
            };
        }

    },

    methods: {

        filterChanged(handle, value) {
            let filters = this.activeFilters;
            if (value) {
                Vue.set(filters, handle, value);
            } else {
                Vue.delete(filters, handle);
            }
            this.$emit('filters-changed', filters);
        },

        save() {
            this.saving = true;

            this.$preferences.set(this.preferencesKey, this.preferencesPayload)
                .then(response => {
                    this.saving = false;
                    this.$notify.success(__('Filters saved'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$notify.error(__('Something went wrong'));
                });
        },

        reset() {
            this.$events.$emit('filters-reset');

            this.saving = true;

            this.$preferences.remove(this.preferencesKey)
                .then(response => {
                    this.saving = false;
                    this.$notify.success(__('Columns reset'));
                })
                .catch(error => {
                    this.saving = false;
                    this.$notify.error(__('Something went wrong'));
                });
        }

    }

}
</script>
