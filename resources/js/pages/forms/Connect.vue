<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Badge, Button, Card, DocsCallout, Header, Heading, Icon, Panel, PanelHeader, Table, TableCell, TableColumn, TableColumns, TableRow, TableRows, ToggleGroup, ToggleItem, publishContextKey } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import { computed, onMounted, provide, ref, watch, watchEffect } from 'vue';
import emailNotificationsLogoRaw from '../../../svg/forms/connect/email-notifications.svg?raw';
import zapierLogoRaw from '../../../svg/forms/connect/zapier.svg?raw';
import iftttLogoRaw from '../../../svg/forms/connect/ifttt.svg?raw';
import ConnectAddRuleButton from './connect-list/ConnectAddRuleButton.vue';
import ConnectRule from './connect-list/ConnectRule.vue';
import { data_set } from '@/bootstrap/globals.js';
import { nanoid as uniqid } from 'nanoid';
import Head from '@/pages/layout/Head.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const mode = ref('grid');
const selectedIntegrationName = ref(null);

function modeChanged(value) {
    mode.value = value;
}

function selectIntegration(integration) {
    selectedIntegrationName.value = integration.name;
}

function clearSelectedIntegration() {
    selectedIntegrationName.value = null;
    mode.value = 'grid';
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
const statamicDeveloperLogo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 139.56 111.63"><path fill="#ff269e" d="M112.46 110.48c9.62-.6 13.78-5.17 14.72-15.49.73-8.04 1-12.06 1.36-20.11.43-9.6 5.28-15.13 9.96-17.7 1.41-.8 1.4-2.65 0-3.54-4.88-2.94-9.61-9.19-9.98-17.45-.36-7.9-.63-11.85-1.35-19.75-1.02-11.13-4.55-14.67-14.18-15.28a793 793 0 0 0-86.42 0c-9.62.61-13.16 4.15-14.18 15.28-.72 7.9-.99 11.85-1.35 19.75-.38 8.26-5.1 14.51-9.98 17.45a2.01 2.01 0 0 0 0 3.54c4.69 2.49 9.53 8.01 9.96 17.7.36 8.04.63 12.06 1.36 20.11.94 10.32 5.1 14.89 14.72 15.49 28.52 1.53 56.85 1.53 85.36 0M69.55 92.63c-8.77.49-19.7-3.8-25.95-9.95-1.23-1.13-1.73-2.53-1.76-3.92-.03-1.11.23-2.31 1.04-3.23.87-1.14 1.31-1.71 2.18-2.85 1.1-1.38 2.3-2.02 3.7-2.01 1.58.01 3.08.67 4.68 1.71 5.06 3.3 10.63 5.2 17.31 5.2 5.1 0 9.78-2.89 9.23-6.57-2.18-14.6-38.37-6.19-37.83-30.63.29-12.86 13.14-21.88 25.75-21.57 9.77.25 17.47 3.09 23.08 6.71 1.4.95 2.37 2.83 2.41 4.69.02 1.12-.23 2.13-.86 3.06-.65 1-.97 1.49-1.63 2.49-1.18 1.66-2.57 2.49-4.34 2.48-1.21 0-2.52-.48-3.92-1.14-4.2-2.17-8.56-3.31-13.85-3.31-5.47 0-9.15 3.52-8.76 6.1 2.18 14.47 37.35 5.97 37.84 30.07.3 14.88-15.58 22.52-28.34 22.68Z"/></svg>';

const integrations = ref([
    {
        name: 'Email Notifications',
        logo: emailNotificationsLogo,
        developerLogo: statamicDeveloperLogo,
        developerName: 'Statamic',
        description: 'Send branded confirmation emails and admin alerts instantly.',
        count: 3,
    },
    {
        name: 'Zapier Workflows',
        logo: zapierLogo,
        developerLogo: statamicDeveloperLogo,
        developerName: 'Statamic',
        description: 'Trigger automations when submissions match your form rules.',
        count: 4,
    },
    {
        name: 'IFTTT Workflows',
        logo: iftttLogo,
        developerLogo: statamicDeveloperLogo,
        developerName: 'Statamic',
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

const selectedIntegration = computed(() => {
    if (!selectedIntegrationName.value) return null;
    return integrations.value.find((integration) => integration.name === selectedIntegrationName.value) ?? null;
});
const connectStateKey = computed(() => {
    const formKey = props.form?.handle ?? props.form?.id ?? props.form?.title ?? 'default';
    return `forms.connect.selectedIntegration.${formKey}`;
});
const fieldPath = 'logic_rules';
const metaPath = 'logic_rules';
const sortableItemClass = 'logic-rule-block';
const loadingSet = ref(null);
const logicBlocks = ref([
    { _id: 'heard_about_us', type: 'heard_about_us', enabled: true, summary: __('How did you hear about us equals Friend referral') },
]);
const collapsed = ref(logicBlocks.value.map((block) => block._id));
const previews = ref({});
const meta = ref({});
const setConfigs = [
    { handle: 'heard_about_us', display: __('Message sent to jack@statamic.com'), conditionDisplay: __('How did you hear about us?'), conditionIcon: 'fieldtype-select', conditionIconClass: 'text-orange-600 dark:text-orange-400', icon: 'mail-sign-at', iconClass: 'text-blue-600 dark:text-blue-400', fields: [] },
    { handle: 'like_most', display: __('What do you like most about our band?'), icon: 'text-long', iconClass: 'text-purple-500 dark:text-purple-400', fields: [] },
    { handle: 'fan_length', display: __('How long have you been a fan?'), icon: 'text-short', iconClass: 'text-purple-500 dark:text-purple-400', fields: [] },
    { handle: 'favorite_album', display: __('Which album was your favorite?'), icon: 'fieldtype-radio', iconClass: 'text-orange-600 dark:text-orange-400', fields: [] },
    { handle: 'second_favorite_album', display: __('Which album was your second favorite?'), icon: 'fieldtype-radio', iconClass: 'text-orange-600 dark:text-orange-400', fields: [] },
    { handle: 'email_notifications_signup', display: __('Sign up for email notifications from The Midnight'), icon: 'fieldtype-checkboxes', iconClass: 'text-orange-600 dark:text-orange-400', fields: [] },
    { handle: 'age', display: __('How old are you?'), icon: 'number', iconClass: 'text-teal-600 dark:text-teal-400', fields: [] },
    { handle: 'free_drink_voucher', display: __('I want a free drink voucher'), icon: 'fieldtype-toggle', iconClass: 'text-orange-600 dark:text-orange-400', fields: [] },
];
const groupConfigs = [
    {
        handle: 'logic',
        display: __('Logic Rules'),
        sets: setConfigs,
    },
];
const setConfigByHandle = computed(() => {
    return setConfigs.reduce((carry, config) => {
        carry[config.handle] = config;
        return carry;
    }, {});
});
const remainingSetConfigs = computed(() => {
    const usedHandles = new Set(logicBlocks.value.map((block) => block.type));
    return setConfigs.filter((config) => !usedHandles.has(config.handle));
});
const remainingGroupConfigs = computed(() => {
    return [
        {
            handle: 'logic',
            display: __('Logic Rules'),
            sets: remainingSetConfigs.value,
        },
    ];
});

watchEffect(() => {
    const nextPreviews = {};
    logicBlocks.value.forEach((block, index) => {
        data_set(nextPreviews, `${fieldPath}.${index}.summary_`, block.summary);
    });
    previews.value = nextPreviews;
});

function setFieldValue(path, value) {
    data_set(logicBlocks.value, path.replace(`${fieldPath}.`, ''), value);
}

function setFieldMeta(path, value) {
    data_set(meta.value, path.replace(`${metaPath}.`, ''), value);
}

function addSet(handle, index = logicBlocks.value.length) {
    if (logicBlocks.value.some((block) => block.type === handle)) return;

    loadingSet.value = handle;
    const config = setConfigByHandle.value[handle];
    const newRule = {
        _id: uniqid(),
        type: handle,
        enabled: true,
        summary: config?.display
            ? __('If :rule has matching conditions, continue to the configured destination.', { rule: config.display })
            : __('No conditions yet.'),
    };

    logicBlocks.value.splice(index, 0, newRule);
    collapsed.value.push(newRule._id);
    loadingSet.value = null;
}

function collapseSet(id) {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
}

function expandSet(id) {
    collapsed.value = collapsed.value.filter((setId) => setId !== id);
}

function removeSet(id) {
    logicBlocks.value = logicBlocks.value.filter((block) => block._id !== id);
    collapsed.value = collapsed.value.filter((setId) => setId !== id);
}

provide('replicatorSets', groupConfigs);
provide(publishContextKey, {
    setFieldValue,
    setFieldMeta,
    previews,
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

onMounted(() => {
    const savedIntegrationName = sessionStorage.getItem(connectStateKey.value);
    if (!savedIntegrationName) return;

    const integrationExists = integrations.value.some((integration) => integration.name === savedIntegrationName);
    if (integrationExists) {
        selectedIntegrationName.value = savedIntegrationName;
    }
});

watch(selectedIntegrationName, (integrationName) => {
    if (integrationName) {
        sessionStorage.setItem(connectStateKey.value, integrationName);
        return;
    }

    sessionStorage.removeItem(connectStateKey.value);
});

</script>

<template>
    <Head :title="[__('Connect'), __(form.title), __('Forms')]" />

    <div class="mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <FormStatusIndicator :status="form?.status" />
                {{ __(formTitle) }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button variant="primary" href="#" icon-append="external-link">
                        {{ __('Explore Integrations') }}
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
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 text-inherit my-0!"
                        :class="selectedIntegrationName ? 'cursor-pointer hover:opacity-80' : 'cursor-default'"
                        :disabled="!selectedIntegrationName"
                        @click="clearSelectedIntegration"
                    >
                        <Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />
                        {{ __('Connect') }}
                    </button>
                    <template v-if="selectedIntegrationName">
                        <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                        <span class="inline-flex items-center gap-1.5">
                            <span
                                v-if="selectedIntegration"
                                class="h-4 w-4 overflow-hidden [&_svg]:h-full [&_svg]:w-full shape-squircle rounded-full"
                                aria-hidden="true"
                            >
                                <span v-html="selectedIntegration.logo" />
                            </span>
                            <span>{{ __(selectedIntegrationName) }}</span>
                        </span>
                    </template>
                </Heading>
            </PanelHeader>
            <Card :class="{ 'p-0!': mode === 'table' && selectedIntegrationName !== 'Email Notifications' }">
                <div v-if="selectedIntegrationName === 'Email Notifications'">
                    <div class="relative space-y-6 mb-0" data-logic-list>
                        <ConnectRule
                            v-for="(block, index) in logicBlocks"
                            :id="block._id"
                            :key="block._id"
                            :index
                            :field-path="fieldPath"
                            :meta-path="metaPath"
                            :values="block"
                            :config="setConfigByHandle[block.type]"
                            :sortable-item-class="sortableItemClass"
                            :collapsed="collapsed.includes(block._id)"
                            :enabled="block.enabled"
                            :read-only="false"
                            :can-add-rule="true"
                            :has-error="false"
                            :show-field-previews="true"
                            @collapsed="collapseSet(block._id)"
                            @expanded="expandSet(block._id)"
                            @removed="removeSet(block._id)"
                        />
                    </div>
                    <ConnectAddRuleButton
                        :groups="remainingGroupConfigs"
                        :sets="remainingSetConfigs"
                        :show-connector="logicBlocks.length > 0"
                        :index="logicBlocks.length"
                        :label="__('Add Email Notification')"
                        :is-first="logicBlocks.length === 0"
                        :loading-set="loadingSet"
                        :search-placeholder="__('Search Fields')"
                        @added="addSet"
                    />
                </div>
                <div v-else-if="mode === 'grid'" class="grid gap-4 grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    <div
                        v-for="integration in integrations"
                        :key="integration.name"
                        class="space-y-2"
                    >
                        <a
                            href="#"
                            class="relative block mb-2 aspect-square rounded-lg border border-gray-300 bg-gray-50/30 p-8 dark:border-gray-700 dark:bg-gray-950/40 dark:hover:bg-gray-900"
                            @click.prevent="selectIntegration(integration)"
                        >
                            <div
                                class="overflow-hidden rounded-full shape-squircle"
                                v-html="integration.logo"
                            />
                        </a>
                        <div class="flex items-center justify-center gap-1.5 text-gray-800 dark:text-gray-200">
                            <Badge v-if="integration.count" size="sm" color="white" pill>
                                {{ integration.count }}
                            </Badge>
                            <span class="truncate text-xs">{{ __(integration.name) }}</span>
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
                                    :text="__('Developer')"
                                    :icon-append="sortIcon('developerName')"
                                    size="sm"
                                    variant="ghost"
                                    class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                    @click.prevent="setSort('developerName')"
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
                                    <a href="#" class="flex min-w-0 items-center gap-2" @click.prevent="selectIntegration(integration)">
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
                                            <span v-html="integration.developerLogo" />
                                        </span>
                                        {{ __(integration.developerName) }}
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

<style scoped>
[data-logic-list]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-logic-list]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
