<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card p-0 relative">
                    <div class="flex items-center justify-between p-2 text-sm border-b">

                        <data-list-search class="h-8" v-if="showFilters" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <data-list-filter-presets
                            ref="presets"
                            v-show="! showFilters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                            @hide-filters="filtersHide"
                            @show-filters="filtersShow"
                        />
                        <div class="flex ml-2 space-x-2">
                            <button class="btn py-1 px-2 h-8" v-text="__('Cancel')" v-show="showFilters" @click="filtersHide" />
                            <button class="btn py-1 px-2 h-8" v-text="__('Save')" v-show="showFilters && isDirty" @click="$refs.presets.savePreset()" />
                            <button class="btn flex items-center py-1 px-2 h-8" @click="handleShowFilters" v-if="! showFilters" v-tooltip="__('Show Filter Controls (F)')">
                                <svg-icon name="search" class="w-4 h-4" />
                                <svg-icon name="filter-lines" class="w-4 h-4" />
                            </button>
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>

                   <div v-show="showFilters">
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :is-searching="showFilters"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @filter-changed="filterChanged"
                            @search-changed="searchChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                            @restore-preset="$refs.presets.viewPreset($event)"
                            @reset="filtersReset"
                        />
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        class="rounded"
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <data-list-table
                        v-show="items.length"
                        :allow-bulk-actions="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                    >
                        <template slot="cell-email" slot-scope="{ row: user, value }">
                            <a :href="user.edit_url" class="flex items-center">
                                <avatar :user="user" class="w-8 h-8 rounded-full mr-2" />
                                {{ value }}
                            </a>
                        </template>
                        <template slot="cell-roles" slot-scope="{ row: user, value: roles }">
                            <span v-if="user.super" class="badge-pill-sm mr-1">{{ __('Super Admin') }}</span>
                            <span v-if="!roles || roles.length === 0" />
                            <span v-for="role in (roles || [])" class="badge-pill-sm mr-1">{{ role.title }}</span>
                        </template>
                        <template slot="cell-groups" slot-scope="{ row: user, value: groups }">
                            <span v-for="group in (groups || [])" class="badge-pill-sm mr-1">{{ group.title }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: user, index }">
                            <dropdown-list placement="left-start" scroll>
                                <dropdown-item :text="__('Edit')" :redirect="user.edit_url" v-if="user.editable" />
                                <dropdown-item :text="__('View')" :redirect="user.edit_url" v-else />
                                <data-list-inline-actions
                                    :item="user.id"
                                    :url="actionUrl"
                                    :actions="user.actions"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                />
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>

                <data-list-pagination
                    class="mt-6"
                    :resource-meta="meta"
                    :per-page="perPage"
                    :show-totals="true"
                    @page-selected="selectPage"
                    @per-page-changed="changePerPage"
                />
            </div>
        </data-list>

    </div>
</template>

<script>
import Listing from '../Listing.vue';

export default {

    mixins: [Listing],

    props: {
        listingKey: String,
        group: String,
    },

    data() {
        return {
            preferencesPrefix: 'users',
            requestUrl: cp_url('users'),
        }
    },

    computed: {

        additionalParameters() {
            return {
                group: this.group,
            }
        }

    }

}
</script>
