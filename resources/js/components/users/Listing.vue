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
            @visible-columns-updated="visibleColumns = $event"
            @selections-updated="selections = $event"
        >
            <div>
                <div class="space-y-3">
                    <!-- Preset Views/Tabs -->
                    <data-list-filter-presets
                        v-if="allowFilterPresets"
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

                    <!-- Search and Filter -->
                    <div class="flex items-center gap-3">
                        <data-list-search ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />
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
                        <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                    </div>

                    <div v-show="items.length === 0" class="p-6 border border-dashed border-gray-300 rounded-lg text-center text-gray-500" v-text="__('No users found')" />

                    <BulkActions
                        :url="actionUrl"
                        :selections="selections"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                        v-slot="{ actions }"
                    >
                        <div class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center">
                            <ButtonGroup>
                                <Button
                                    variant="primary"
                                    class="text-gray-400!"
                                    :text="__n(`:count item selected|:count items selected`, selections.length)"
                                />
                                <Button
                                    v-for="action in actions"
                                    :key="action.handle"
                                    variant="primary"
                                    :text="__(action.title)"
                                    @click="action.run"
                                />
                            </ButtonGroup>
                        </div>
                    </BulkActions>

                    <Panel class="relative overflow-x-auto overscroll-x-contain" v-if="items.length">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="true"
                            :loading="loading"
                            :sortable="true"
                            :toggle-selection-on-row-click="true"
                            @sorted="sorted"
                        >
                            <template #cell-email="{ row: user }">
                                <a class="title-index-field" :href="user.edit_url" @click.stop>
                                    <avatar :user="user" class="h-8 w-8 rounded-full ltr:mr-2 rtl:ml-2" />
                                    <span v-text="user.email" />
                                </a>
                            </template>
                            <template #cell-roles="{ row: user, value: roles }">
                                <div class="role-index-field">
                                    <div v-if="user.super" class="role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1">
                                        {{ __('Super Admin') }}
                                    </div>
                                    <div v-if="!roles || roles.length === 0" />
                                    <div
                                        v-for="(role, i) in roles || []"
                                        class="role-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1"
                                    >
                                        {{ __(role.title) }}
                                    </div>
                                </div>
                            </template>
                            <template #cell-groups="{ row: user, value: groups }">
                                <div class="groups-index-field">
                                    <div
                                        v-for="group in groups || []"
                                        class="groups-index-field-item mb-1.5 ltr:mr-1 rtl:ml-1"
                                    >
                                        {{ __(group.title) }}
                                    </div>
                                </div>
                            </template>
                            <template #cell-two_factor="{ row: user, value }">
                                <div class="flex items-center space-x-2">
                                    <template v-if="value">
                                        <svg-icon name="light/check" class="w-3 text-green-600" />
                                    </template>
                                    <template v-else>
                                        <svg-icon name="light/close" class="w-3 text-gray-500" />
                                    </template>
                                </div>
                            </template>
                            <template #actions="{ row: user, index }">
                                <ItemActions
                                    :url="actionUrl"
                                    :actions="user.actions"
                                    :item="user.id"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                    v-slot="{ actions }"
                                >
                                    <Dropdown placement="left-start" class="me-3">
                                        <DropdownMenu>
                                            <DropdownLabel :text="__('Actions')" />
                                            <DropdownItem
                                                :text="__('Edit')"
                                                :href="user.edit_url"
                                                icon="edit"
                                                v-if="user.editable"
                                            />
                                            <DropdownItem
                                                :text="__('View')"
                                                :href="user.edit_url"
                                                icon="eye"
                                                v-else
                                            />
                                            <DropdownSeparator v-if="actions.length" />
                                            <DropdownItem
                                                v-for="action in actions"
                                                :key="action.handle"
                                                :text="__(action.title)"
                                                :icon="action.icon"
                                                :variant="action.dangerous ? 'destructive' : 'default'"
                                                @click="action.run"
                                            />
                                        </DropdownMenu>
                                    </Dropdown>
                                </ItemActions>
                            </template>
                        </data-list-table>
                    </Panel>
                </div>
                <data-list-pagination
                    class="mt-3"
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
import {
    Button,
    Panel,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownLabel,
    DropdownSeparator,
    ButtonGroup,
} from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import BulkActions from '@statamic/components/actions/BulkActions.vue';

export default {
    mixins: [Listing],

    components: {
        Button,
        Panel,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        DropdownSeparator,
        ButtonGroup,
        ItemActions,
        BulkActions,
    },

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
        };
    },

    computed: {
        actionContext() {
            return { group: this.group };
        },
        additionalParameters() {
            return {
                group: this.group,
            };
        },
    },
};
</script>
