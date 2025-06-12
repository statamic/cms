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
                <div class="card relative overflow-hidden p-0">
                    <div
                        class="flex flex-wrap items-center justify-between border-b px-2 pb-2 text-sm dark:border-dark-900"
                    >
                        <data-list-filter-presets
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

                        <data-list-search
                            class="mt-2 h-8 w-full min-w-[240px]"
                            ref="search"
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                        />

                        <div class="mt-2 flex space-x-2 rtl:space-x-reverse">
                            <button
                                class="btn btn-sm ltr:ml-2 rtl:mr-2"
                                v-text="__('Reset')"
                                v-show="isDirty"
                                @click="$refs.presets.refreshPreset()"
                            />
                            <button
                                class="btn btn-sm ltr:ml-2 rtl:mr-2"
                                v-text="__('Save')"
                                v-show="isDirty"
                                @click="$refs.presets.savePreset()"
                            />
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

                    <data-list-table
                        v-show="items.length"
                        :loading="loading"
                        :allow-bulk-actions="true"
                        :allow-column-picker="true"
                        :column-preferences-key="preferencesKey('columns')"
                        @sorted="sorted"
                    >
                        <template #cell-title="{ row: term }">
                            <div class="flex items-center">
                                <a :href="term.edit_url">{{ term.title }}</a>
                            </div>
                        </template>
                        <template #cell-slug="{ row: term }">
                            <span class="font-mono text-2xs">{{ term.slug }}</span>
                        </template>
                        <template #actions="{ row: term, index }">
                            <ItemActions
                                :url="actionUrl"
                                :actions="term.actions"
                                :item="term.id"
                                @started="actionStarted"
                                @completed="actionCompleted"
                                v-slot="{ actions }"
                            >
                                <Dropdown placement="left-start" class="me-3">
                                    <DropdownMenu>
                                        <DropdownLabel :text="__('Actions')" />
                                        <DropdownItem :text="__('Visit URL')" :href="term.permalink" icon="eye" />
                                        <DropdownItem :text="__('Edit')" :href="term.edit_url" icon="edit" />
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
                </div>
                <data-list-pagination
                    class="mt-6"
                    :resource-meta="meta"
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
    ButtonGroup,
    Dropdown,
    DropdownItem,
    DropdownLabel,
    DropdownMenu,
    DropdownSeparator,
} from '@statamic/ui';
import BulkActions from '@statamic/components/actions/BulkActions.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    mixins: [Listing],

    components: {
        Button,
        ButtonGroup,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        DropdownSeparator,
        DropdownItem,
        BulkActions,
        ItemActions
    },

    props: {
        taxonomy: String,
    },

    data() {
        return {
            listingKey: 'terms',
            preferencesPrefix: `taxonomies.${this.taxonomy}`,
            requestUrl: cp_url(`taxonomies/${this.taxonomy}/terms`),
            pushQuery: true,
        };
    },

    computed: {
        actionContext() {
            return { taxonomy: this.taxonomy };
        },
    },

    methods: {
        preferencesKey(type) {
            return `taxonomies.${this.taxonomy}.${type}`;
        },
    },
};
</script>
