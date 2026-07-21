<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '../Layout.vue';
import Head from '@/pages/layout/Head.vue';
import {
    Badge,
    Button,
    Card,
    DocsCallout,
    Header,
    Heading,
    Icon,
    Panel,
    PanelHeader,
    ToggleGroup,
    ToggleItem,
    Listing,
    ListingTable,
} from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { __ } from '@/bootstrap/globals';
import { preferences } from '@api';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

enum View {
    Grid = 'grid',
    List = 'list',
}

type Connection = {
    handle: string;
    title: string;
    description: string;
    icon: string;
    developer: string;
    count: number;
    url: string;
}

const props = defineProps<{
    form: Object,
    connections: Connection[],
}>();

const view = ref<View>(preferences.get('forms.connect.view', View.Grid));

watch(view, (view: View) => preferences.set('forms.connect.view', view));
</script>

<style>
#connections-listing tbody td {
    @apply rounded-t-none border-x-0;
}
</style>

<template>
    <Head :title="[__('Connect'), __(form.title), __('Forms')]" />

    <div class="mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button variant="primary" href="https://statamic.com/addons?category=forms" target="_blank" rel="noopener" icon-append="external-link">
                        {{ __('Browse the Marketplace') }}
                    </Button>
                    <ToggleGroup v-model="view">
                        <ToggleItem :value="View.Grid" icon="layout-grid" />
                        <ToggleItem :value="View.List" icon="layout-list" />
                    </ToggleGroup>
                </div>
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <Heading>
                    <span class="inline-flex items-center gap-2">
                        <Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />
                        {{ __('Connect') }}
                    </span>
                </Heading>
            </PanelHeader>
            <Card :class="{ 'p-0!': view === View.List }">
                <div v-if="view === View.Grid" class="grid gap-4 grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    <div
                        v-for="connection in connections"
                        :key="connection.handle"
                        class="space-y-2"
                    >
                        <Link
                            :href="connection.url"
                            class="relative flex mb-2 aspect-square items-center justify-center rounded-lg border border-gray-300 bg-gray-50/30 p-8 text-gray-700 hover:bg-gray-100/50 dark:border-gray-700 dark:bg-gray-950/40 dark:text-gray-300 dark:hover:bg-gray-900"
                        >
                            <span class="[&_svg]:size-12" aria-hidden="true" v-html="connection.icon" />
                        </Link>
                        <div class="flex items-center justify-center gap-1.5 text-gray-800 dark:text-gray-200">
                            <Badge v-if="connection.count" size="sm" color="white" pill>
                                {{ connection.count }}
                            </Badge>
                            <span class="truncate text-xs">{{ __(connection.title) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <Listing
                        id="connections-listing"
                        class="pt-1"
                        :items="connections"
                        :columns="[
                            { field: 'title', label: __('Connection'), sortable: true, visible: true },
                            { field: 'description', label: __('Description'), sortable: false, visible: true },
                            { field: 'developer', label: __('Developer'), sortable: true, visible: true },
                        ]"
                        :searchable="false"
                        :allow-customizing-columns="false"
                    >
                        <ListingTable>
                            <template #cell-title="{ row: connection }">
                                <Link :href="connection.url" class="flex min-w-0 items-center gap-2">
                                    <span class="size-7 flex items-center justify-center text-gray-700 dark:text-gray-300 [&_svg]:size-5" aria-hidden="true" v-html="connection.icon" />
                                    <Badge v-if="connection.count" size="sm" color="white" pill>
                                        {{ connection.count }}
                                    </Badge>
                                    <span class="truncate text-sm text-gray-800 dark:text-gray-200">{{ __(connection.title) }}</span>
                                </Link>
                            </template>
                        </ListingTable>
                    </Listing>
                </div>
            </Card>
        </Panel>

        <DocsCallout :topic="__('Connections')" url="forms" />
    </div>
</template>
