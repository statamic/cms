<template>
    <ui-header :title="__('Collections')" icon="collections">
        <ui-toggle-group v-model="mode">
            <ui-toggle-item icon="layout-grid" value="grid" />
            <ui-toggle-item icon="layout-list" value="table" />
        </ui-toggle-group>
        <ui-button
            :href="createUrl"
            :text="__('Create Collection')"
            variant="primary"
            v-if="canCreateCollections"
        />
    </ui-header>
    <div class="@container/collections flex flex-wrap py-2 gap-y-6 -mx-3" v-if="mode === 'grid'">
        <div v-for="collection in items" class="w-full @4xl:w-1/2 px-3">
            <ui-panel>
                <ui-panel-header class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <ui-heading size="lg" :text="__(collection.title)" :href="collection.available_in_selected_site ? collection.entries_url : collection.edit_url" />
                        <span class="text-sm text-gray-500">
                            ({{ __('entry_count', { count: collection.entries_count }) }})
                        </span>
                    </div>
                    <aside class="flex items-center gap-2">
                        <dropdown-list placement="left-start">
                            <dropdown-item :text="__('View')" :redirect="collection.entries_url" />
                            <dropdown-item v-if="collection.url" :text="__('Visit URL')" :external-link="collection.url" />
                            <dropdown-item
                                v-if="collection.editable"
                                :text="__('Edit Collection')"
                                :redirect="collection.edit_url"
                            />
                            <dropdown-item
                                v-if="collection.blueprint_editable"
                                :text="__('Edit Blueprints')"
                                :redirect="collection.blueprints_url"
                            />
                            <dropdown-item
                                v-if="collection.editable"
                                :text="__('Scaffold Views')"
                                :redirect="collection.scaffold_url"
                            />
                            <data-list-inline-actions
                                :item="collection.id"
                                :url="collection.actions_url"
                                :actions="collection.actions"
                                @completed="actionCompleted"
                            ></data-list-inline-actions>
                        </dropdown-list>
                        <create-entry-button
                            :url="collection.create_entry_url"
                            variant="default"
                            :blueprints="collection.blueprints"
                            :text="__('Create Entry')"
                            size="sm"
                            class="-mr-2"
                        />
                    </aside>
                </ui-panel-header>

                <ui-card>
                    <data-list :rows="collection.entries" :columns="collection.columns" :sort="false">
                        <data-list-table unstyled class="[&_td]:p-0.5 [&_td]:text-sm [&_thead]:hidden w-full">
                            <template #cell-title="{ row: entry }" class="w-full">
                                <div class="flex items-center gap-2">
                                    <StatusIndicator :status="entry.status" />
                                    <a :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis" :text="entry.title" />
                                </div>
                            </template>
                            <template #cell-date="{ row: entry }" v-if="collection.dated">
                                <div class="text-end font-mono text-gray-400 ps-6">
                                    <date-time :of="entry.date" date-only />
                                </div>
                            </template>
                        </data-list-table>
                    </data-list>
                </ui-card>

                <ui-panel-footer class="flex items-center gap-6 text-sm text-gray-500">
                    <div class="flex items-center gap-2" v-if="collection.published_entries_count > 0">
                        <ui-badge variant="flat" :text="String(collection.published_entries_count)" pill class="bg-gray-200 dark:bg-gray-700" />
                        <span>{{ __('Published') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm" v-if="collection.scheduled_entries_count > 0">
                        <ui-badge variant="flat" :text="String(collection.scheduled_entries_count)" pill class="bg-gray-200 dark:" />
                        <span>{{ __('Scheduled') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm" v-if="collection.draft_entries_count > 0">
                        <ui-badge variant="flat" :text="String(collection.draft_entries_count)" pill class="bg-gray-200 dark:" />
                        <span>{{ __('Draft') }}</span>
                    </div>
                </ui-panel-footer>
            </ui-panel>
        </div>
    </div>

    <data-list ref="dataList" :columns="columns" :rows="items" v-if="mode === 'table'">
        <data-list-table>
            <template #cell-title="{ row: collection }">
                <a :href="collection.available_in_selected_site ? collection.entries_url : collection.edit_url" class="flex items-center gap-2">
                    <ui-icon :name="collection.icon || 'collections'" />
                    {{ __(collection.title) }}
                </a>
            </template>
            <template #actions="{ row: collection, index }">
                <dropdown-list placement="left-start">
                    <dropdown-item :text="__('View')" :redirect="collection.entries_url" />
                    <dropdown-item v-if="collection.url" :text="__('Visit URL')" :external-link="collection.url" />
                    <dropdown-item
                        v-if="collection.editable"
                        :text="__('Edit Collection')"
                        :redirect="collection.edit_url"
                    />
                    <dropdown-item
                        v-if="collection.blueprint_editable"
                        :text="__('Edit Blueprints')"
                        :redirect="collection.blueprints_url"
                    />
                    <dropdown-item
                        v-if="collection.editable"
                        :text="__('Scaffold Views')"
                        :redirect="collection.scaffold_url"
                    />
                    <data-list-inline-actions
                        :item="collection.id"
                        :url="collection.actions_url"
                        :actions="collection.actions"
                        @completed="actionCompleted"
                    ></data-list-inline-actions>
                </dropdown-list>
            </template>
        </data-list-table>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { CardPanel, StatusIndicator, Badge } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: { CardPanel, StatusIndicator, Badge },

    props: {
        canCreateCollections: Boolean,
        createUrl: String,
        initialRows: Array,
        initialColumns: Array,
    },

    data() {
        return {
            initializedRequest: false,
            items: this.initialRows,
            columns: this.initialColumns,
            requestUrl: cp_url(`collections`),
            mode: 'table',
        };
    },

    methods: {
        request() {
            // If we have initial data, we don't need to perform a request.
            // Subsequent requests, like after performing actions, we do want to perform a request.
            if (!this.initializedRequest) {
                this.loading = false;
                this.initializedRequest = true;
                return;
            }

            Listing.methods.request.call(this);
        },
    },
};
</script>
