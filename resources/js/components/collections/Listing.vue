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
                        <span class="text-sm text-gray-600">
                            ({{ __('entry_count', { count: collection.entries_count }) }})
                        </span>
                    </div>
                    <aside class="flex items-center gap-2">
                        <ItemActions
                            :url="collection.actions_url"
                            :actions="collection.actions"
                            :item="collection.id"
                            @started="actionStarted"
                            @completed="actionCompleted"
                            v-slot="{ actions }"
                        >
                            <Dropdown placement="left-start" class="me-3">
                                <DropdownMenu>
                                    <DropdownItem :text="__('View')" icon="eye" :href="collection.entries_url" />
                                    <DropdownItem v-if="collection.url" :text="__('Visit URL')" icon="external-link" target="_blank" :href="collection.url" />
                                    <DropdownItem v-if="collection.editable" :text="__('Configure')" icon="cog" :href="collection.edit_url" />
                                    <DropdownItem v-if="collection.blueprint_editable" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="collection.blueprints_url" />
                                    <DropdownItem v-if="collection.editable" :text="__('Scaffold Views')" icon="scaffold" :href="collection.scaffold_url" />
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

                <ui-card class="h-40">
                    <ui-listing :items="collection.entries" :columns="collection.columns">
                        <table class="w-full [&_td]:p-0.5 [&_td]:text-sm">
                            <ui-listing-table-head sr-only />
                            <ui-listing-table-body>
                                <template #cell-title="{ row: entry }" class="w-full">
                                    <div class="flex items-center gap-2">
                                        <StatusIndicator :status="entry.status" />
                                        <a :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis" :text="entry.title" />
                                    </div>
                                </template>
                                <template #cell-date="{ row: entry }" v-if="collection.dated">
                                    <div class="text-end font-mono text-xs text-gray-500 ps-6">
                                        <date-time :of="entry.date" date-only />
                                    </div>
                                </template>
                            </ui-listing-table-body>
                        </table>
                    </ui-listing>
                    <ui-subheading v-if="collection.entries.length === 0" class="text-center h-full flex items-center justify-center">{{ __('Nothing to see here, yet.') }}</ui-subheading>
                </ui-card>

                <ui-panel-footer class="flex items-center gap-6 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
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

    <ui-listing
        v-if="mode === 'table'"
        :items="items"
        :columns="columns"
        :action-url="actionUrl"
        :allow-search="false"
        :allow-customizing-columns="false"
        @refreshing="request"
    >
        <template #cell-title="{ row: collection }">
            <a :href="collection.available_in_selected_site ? collection.entries_url : collection.edit_url" class="flex items-center gap-2">
                <ui-icon :name="collection.icon || 'collections'" />
                {{ __(collection.title) }}
            </a>
        </template>
        <template #cell-entries_count="{ row: collection }">
            <div class="flex items-center gap-3">
                <ui-badge
                    v-if="collection.published_entries_count > 0"
                    color="green"
                    :text="String(collection.published_entries_count) + ' ' + __('Published')"
                    pill
                />
                <ui-badge
                    v-if="collection.scheduled_entries_count > 0"
                    color="yellow"
                    :text="String(collection.scheduled_entries_count) + ' ' + __('Scheduled')"
                    pill
                />
                <ui-badge
                    v-if="collection.draft_entries_count > 0"
                    :text="String(collection.draft_entries_count) + ' ' + __('Draft')"
                    pill
                />
            </div>
        </template>
        <template #prepended-row-actions="{ row: collection }">
            <DropdownItem :text="__('View')" icon="eye" :href="collection.entries_url" />
            <DropdownItem v-if="collection.url" :text="__('Visit URL')" icon="external-link" target="_blank" :href="collection.url" />
            <DropdownItem v-if="collection.editable" :text="__('Configure')" icon="cog" :href="collection.edit_url" />
            <DropdownItem v-if="collection.blueprint_editable" :text="__('Edit Blueprints')" icon="blueprint-edit" :href="collection.blueprints_url" />
            <DropdownItem v-if="collection.editable" :text="__('Scaffold Views')" icon="scaffold" :href="collection.scaffold_url" />
        </template>
    </ui-listing>
</template>

<script>
import {
    CardPanel,
    StatusIndicator,
    Badge,
    Dropdown,
    DropdownMenu,
    DropdownLabel,
    DropdownItem,
    DropdownSeparator,
} from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    components: {
        CardPanel,
        StatusIndicator,
        Badge,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        DropdownItem,
        DropdownSeparator,
        ItemActions,
    },

    props: {
        canCreateCollections: Boolean,
        createUrl: String,
        initialRows: Array,
        initialColumns: Array,
        actionUrl: String,
    },

    data() {
        return {
            initializedRequest: false,
            items: this.initialRows,
            columns: this.initialColumns,
            requestUrl: cp_url(`collections`),
            mode: this.$preferences.get('collections.listing_mode', 'grid'),
            source: null,
        };
    },

    watch: {
        mode(mode) {
            this.$preferences.set('collections.listing_mode', mode);
        },
    },

    mounted() {
        this.addToCommandPalette();
    },

    methods: {
        request() {
            if (this.source) this.source.abort();
            this.source = new AbortController();

            this.$axios
                .get(this.requestUrl, {
                    params: this.parameters,
                    signal: this.source.signal,
                })
                .then((response) => {
                    this.columns = response.data.meta.columns;
                    this.items = Object.values(response.data.data);
                    this.meta = response.data.meta;
                    this.loading = false;
                })
                .catch((e) => {
                    if (this.$axios.isCancel(e)) return;
                    this.loading = false;
                    this.initializing = false;
                    if (e.request && !e.response) return;
                    this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), {
                        duration: null,
                    });
                });
        },

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful, response) {
            // Intentionally not completing the loading state here since
            // the listing will refresh and immediately restart it.
            // this.loading = false;

            successful
                ? this.$toast.success(response.message || __('Action completed'))
                : this.$toast.error(response.message || __('Action failed'));

            this.request();
        },

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Create Collection'),
                icon: 'collections',
                when: () => this.canCreateCollections,
                url: this.createUrl,
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Toggle Grid Layout'),
                when: () => this.mode === 'table',
                action: () => this.mode = 'grid',
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Toggle List Layout'),
                when: () => this.mode === 'grid',
                action: () => this.mode = 'table',
            });

            // TODO: We can add more two-step actions later.
            // ie. With the 'Configure' / 'Edit Blueprints' / 'Scaffold Views' stuff in the twirldowns,
            // the user should be able to select a collection in the palette?
        },
    },
};
</script>
