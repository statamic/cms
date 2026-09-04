<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '../Layout.vue';
import Head from '@/pages/layout/Head.vue';
import { Alert, Button, Card, DocsCallout, Header, Heading, Icon, Panel, PanelHeader, ToggleGroup, ToggleItem } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import ConnectionsListing, { View } from '@/components/forms/connections/ConnectionsListing.vue';
import { ref, watch } from 'vue';
import { preferences } from '@api';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

type Connection = {
    handle: string;
    title: string;
    description: string;
    icon: string;
    developer: string;
    count: number | null;
    url: string;
}

defineProps<{
    form: Object,
    connections: Connection[],
    uniqueInstancesEnabled: boolean,
}>();

const view = ref<View>(preferences.get('forms.connect.view', View.Grid));

watch(view, (view) => preferences.set('forms.connect.view', view));
</script>

<template>
    <Head :title="[__('Connect'), __(form.title), __('Forms')]" />

    <div class="mx-auto max-w-5xl">
        <Header>
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button variant="primary" href="https://statamic.com/addons?category=forms" target="_blank" rel="noopener" icon-append="external-link">
                        {{ __('Explore Integrations') }}
                    </Button>
                    <ToggleGroup v-model="view">
                        <ToggleItem :value="View.Grid" icon="layout-grid" />
                        <ToggleItem :value="View.List" icon="layout-list" />
                    </ToggleGroup>
                </div>
            </template>
        </Header>

        <Alert
            v-if="uniqueInstancesEnabled"
            variant="warning"
            class="mb-6"
            :text="__('messages.form_connect_unique_instances_warning')"
        />

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
                <ConnectionsListing :connections :view />
            </Card>
        </Panel>

        <DocsCallout :topic="__('Connections')" url="forms" />
    </div>
</template>
