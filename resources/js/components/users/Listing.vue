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
            v-slot="{ hasSelections }"
        >
            <div>
                <div class="card overflow-hidden p-0 relative">
                    <div class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b dark:border-dark-900">

                        <data-list-filter-presets
                            v-show="allowFilterPresets"
                            ref="presets"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                        />

                        <data-list-search class="h-8 mt-2 min-w-[240px] w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <div class="flex space-x-2 rtl:space-x-reverse mt-2">
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Reset')" v-show="isDirty" @click="$refs.presets.refreshPreset()" />
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Save')" v-show="allowFilterPresets && isDirty" @click="$refs.presets.savePreset()" />
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>

                    <data-list-filters
                        ref="filters"
                        :filters="filters"
                        :active-preset="activePreset"
                        :active-preset-payload="activePresetPayload"
                        :active-filters="activeFilters"
                        :active-filter-badges="activeFilterBadges"
                        :active-count="activeFilterCount"
                        :search-query="searchQuery"
                        :is-searching="true"
                        :saves-presets="true"
                        :preferences-prefix="preferencesPrefix"
                        @changed="filterChanged"
                        @saved="$refs.presets.setPreset($event)"
                        @deleted="$refs.presets.refreshPresets()"
                    />

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        class="rounded"
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <div class="overflow-x-auto overflow-y-hidden">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="true"
                            :allow-column-picker="true"
                            :column-preferences-key="preferencesKey('columns')"
                            @sorted="sorted"
                        >
                            <template #cell-email="{ row: user, value }">
                                <a :href="user.edit_url" class="flex items-center">
                                    <avatar :user="user" class="w-8 h-8 rounded-full rtl:ml-2 ltr:mr-2" />
                                    {{ value }}
                                </a>
                            </template>
                            <template #cell-roles="{ row: user, value: roles }">
                                <div class="role-index-field">
                                    <div v-if="user.super" class="role-index-field-item rtl:ml-1 ltr:mr-1 mb-1.5">{{ __('Super Admin') }}</div>
                                    <div v-if="!roles || roles.length === 0" />
                                    <div v-for="(role, i) in (roles || [])" class="role-index-field-item rtl:ml-1 ltr:mr-1 mb-1.5">{{ __(role.title) }}</div>
                                </div>
                            </template>
                            <template #cell-groups="{ row: user, value: groups }">
                                <div class="groups-index-field">
                                    <div v-for="group in (groups || [])" class="groups-index-field-item rtl:ml-1 ltr:mr-1 mb-1.5">{{ __(group.title) }}</div>
                                </div>
                            </template>
                            <template #actions="{ row: user, index }">
                                <dropdown-list placement="right-start">
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
        allowFilterPresets: {
            default: true,
        },
    },

    data() {
        return {
            preferencesPrefix: 'users',
            requestUrl: cp_url('users'),
            pushQuery: true,
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
