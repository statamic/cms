<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Card, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator } from '@ui';
import { computed } from 'vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
</script>

<template>
    <div class="py-4 mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
        </Header>

        <Panel>
            <PanelHeader>
                <Heading>
                    <span>{{ __('Connect') }}</span>
                    <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <span>{{ __('Email Notifications') }}</span>
                </Heading>
            </PanelHeader>
            <Card class="min-h-32"></Card>
        </Panel>
    </div>
</template>
