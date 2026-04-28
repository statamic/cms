<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Badge, Button, Card, DocsCallout, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator, Table, TableCell, TableColumn, TableColumns, TableRow, TableRows, ToggleGroup, ToggleItem } from '@ui';
import { computed, ref } from 'vue';
import emailNotificationsLogoRaw from '../../../svg/forms/connect/email-notifications.svg?raw';
import zapierLogoRaw from '../../../svg/forms/connect/zapier.svg?raw';
import iftttLogoRaw from '../../../svg/forms/connect/ifttt.svg?raw';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const mode = ref('grid');

function modeChanged(value) {
    mode.value = value;
}
const emailNotificationsLogo = emailNotificationsLogoRaw
    .replace(/<\?xml[\s\S]*?\?>\s*/i, '')
    .replace(/<!DOCTYPE[\s\S]*?>\s*/i, '');
const zapierLogo = zapierLogoRaw
    .replace(/<\?xml[\s\S]*?\?>\s*/i, '')
    .replace(/<!DOCTYPE[\s\S]*?>\s*/i, '');
const iftttLogo = iftttLogoRaw
    .replace(/<\?xml[\s\S]*?\?>\s*/i, '')
    .replace(/<!DOCTYPE[\s\S]*?>\s*/i, '');
const statamicVendorLogo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 139.56 111.63"><path fill="#ff269e" d="M112.46 110.48c9.62-.6 13.78-5.17 14.72-15.49.73-8.04 1-12.06 1.36-20.11.43-9.6 5.28-15.13 9.96-17.7 1.41-.8 1.4-2.65 0-3.54-4.88-2.94-9.61-9.19-9.98-17.45-.36-7.9-.63-11.85-1.35-19.75-1.02-11.13-4.55-14.67-14.18-15.28a793 793 0 0 0-86.42 0c-9.62.61-13.16 4.15-14.18 15.28-.72 7.9-.99 11.85-1.35 19.75-.38 8.26-5.1 14.51-9.98 17.45a2.01 2.01 0 0 0 0 3.54c4.69 2.49 9.53 8.01 9.96 17.7.36 8.04.63 12.06 1.36 20.11.94 10.32 5.1 14.89 14.72 15.49 28.52 1.53 56.85 1.53 85.36 0M69.55 92.63c-8.77.49-19.7-3.8-25.95-9.95-1.23-1.13-1.73-2.53-1.76-3.92-.03-1.11.23-2.31 1.04-3.23.87-1.14 1.31-1.71 2.18-2.85 1.1-1.38 2.3-2.02 3.7-2.01 1.58.01 3.08.67 4.68 1.71 5.06 3.3 10.63 5.2 17.31 5.2 5.1 0 9.78-2.89 9.23-6.57-2.18-14.6-38.37-6.19-37.83-30.63.29-12.86 13.14-21.88 25.75-21.57 9.77.25 17.47 3.09 23.08 6.71 1.4.95 2.37 2.83 2.41 4.69.02 1.12-.23 2.13-.86 3.06-.65 1-.97 1.49-1.63 2.49-1.18 1.66-2.57 2.49-4.34 2.48-1.21 0-2.52-.48-3.92-1.14-4.2-2.17-8.56-3.31-13.85-3.31-5.47 0-9.15 3.52-8.76 6.1 2.18 14.47 37.35 5.97 37.84 30.07.3 14.88-15.58 22.52-28.34 22.68Z"/></svg>';

const integrations = ref([
    {
        name: 'Email Notifications',
        logo: emailNotificationsLogo,
        vendorLogo: statamicVendorLogo,
        vendorName: 'Statamic',
        description: 'Send branded confirmation emails and admin alerts instantly.',
        count: 3,
    },
    {
        name: 'Zapier Workflows',
        logo: zapierLogo,
        vendorLogo: statamicVendorLogo,
        vendorName: 'Statamic',
        description: 'Trigger automations when submissions match your form rules.',
        count: 4,
    },
    {
        name: 'IFTTT Workflows',
        logo: iftttLogo,
        vendorLogo: statamicVendorLogo,
        vendorName: 'Statamic',
        description: 'Connect lightweight applets for notifications and logging.',
        count: 1,
    },
]);
const sortColumn = ref('name');
const sortDirection = ref('asc');

const sortedIntegrations = computed(() => {
    const sorted = [...integrations.value].sort((a, b) => {
        const left = String(a[sortColumn.value] ?? '').toLowerCase();
        const right = String(b[sortColumn.value] ?? '').toLowerCase();
        return left.localeCompare(right);
    });

    return sortDirection.value === 'desc' ? sorted.reverse() : sorted;
});

function setSort(column) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        return;
    }

    sortColumn.value = column;
    sortDirection.value = 'asc';
}

function sortIcon(column) {
    if (sortColumn.value !== column) return null;
    return sortDirection.value === 'asc' ? 'sort-asc' : 'sort-desc';
}

</script>

<template>
    <div class="py-4 mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2">
                    <Button variant="primary" href="#" icon-append="external-link">
                        {{ __('Browse the Marketplace') }}
                    </Button>
                    <ToggleGroup :model-value="mode" @update:model-value="modeChanged">
                        <ToggleItem value="grid" icon="layout-grid" />
                        <ToggleItem value="table" icon="layout-list" />
                    </ToggleGroup>
                </div>
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <Heading>
                    <span class="inline-flex items-center gap-2"><Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />{{ __('Connect') }}</span>
                    <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <span>{{ __('Email Notifications') }}</span>
                </Heading>
            </PanelHeader>
            <Card :class="{ 'p-0!': mode === 'table' }">
                <div v-if="mode === 'grid'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <div
                        v-for="integration in integrations"
                        :key="integration.name"
                        class="space-y-2"
                    >
                        <a
                            href="#"
                            class="relative block mb-2 aspect-square rounded-lg border border-gray-300 bg-gray-50/30 p-8 dark:border-gray-700 dark:bg-gray-950/40 dark:hover:bg-gray-900"
                        >
                            <Badge
                                v-if="integration.count"
                                size="sm"
                                color="white"
                                pill
                                class="absolute top-1 right-1"
                            >
                                {{ integration.count }}
                            </Badge>
                            <div
                                class="overflow-hidden rounded-full shape-squircle"
                                v-html="integration.logo"
                            />
                        </a>
                        <div class="flex items-center gap-1.5 text-sm text-gray-800 dark:text-gray-200">  
                            <span class="h-5 w-5 shrink-0 overflow-hidden [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                                <span v-html="integration.vendorLogo" />
                            </span>
                            <span class="truncate">{{ __(integration.name) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <Table class="w-full">
                        <TableColumns>
                            <TableColumn class="first:pl-4 last:pr-4">
                                <Button
                                    :text="__('Integration')"
                                    :icon-append="sortIcon('name')"
                                    size="sm"
                                    variant="ghost"
                                    class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                    @click.prevent="setSort('name')"
                                />
                            </TableColumn>
                            <TableColumn class="first:pl-4 last:pr-4">
                                <Button
                                    :text="__('Description')"
                                    :icon-append="sortIcon('description')"
                                    size="sm"
                                    variant="ghost"
                                    class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                    @click.prevent="setSort('description')"
                                />
                            </TableColumn>
                            <TableColumn class="text-right first:pl-4 last:pr-4">
                                <Button
                                    :text="__('Vendor')"
                                    :icon-append="sortIcon('vendorName')"
                                    size="sm"
                                    variant="ghost"
                                    class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                    @click.prevent="setSort('vendorName')"
                                />
                            </TableColumn>
                        </TableColumns>
                            <TableRows>
                            <TableRow
                                v-for="integration in sortedIntegrations"
                                :key="`list-${integration.name}`"
                                class="hover:bg-gray-50 dark:hover:bg-gray-950/35"
                            >
                                <TableCell class="first:pl-4 last:pr-4">
                                    <a href="#" class="flex min-w-0 items-center gap-2">
                                        <span class="h-7 w-7 overflow-hidden rounded-full shape-squircle [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                                            <span v-html="integration.logo" />
                                        </span>
                                        <Badge v-if="integration.count" size="sm" color="white" pill>
                                            {{ integration.count }}
                                        </Badge>
                                        <span class="truncate text-sm text-gray-800 dark:text-gray-200">{{ __(integration.name) }}</span>
                                    </a>
                                </TableCell>
                                <TableCell class="first:pl-4 last:pr-4">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __(integration.description) }}</span>
                                </TableCell>
                                <TableCell class="text-right first:pl-4 last:pr-4">
                                    <span class="inline-flex shrink-0 items-center text-sm text-gray-800 dark:text-gray-200">
                                        <span class="h-4 w-4 overflow-hidden me-1 [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                                            <span v-html="integration.vendorLogo" />
                                        </span>
                                        {{ __(integration.vendorName) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                            </TableRows>
                    </Table>
                </div>
            </Card>
        </Panel>
        <DocsCallout :topic="__('Connections')" url="forms" />
    </div>
</template>
