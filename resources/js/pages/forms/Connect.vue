<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Badge, Card, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator } from '@ui';
import { computed } from 'vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));

const integrations = [
    {
        name: 'Email Notifications',
        logo: '✉',
        logoClass: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300',
        count: 3,
    },
    {
        name: 'Zapier Workflows',
        logo: 'Z',
        logoClass: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
        count: null,
    },
    {
        name: 'IFTTT Workflows',
        logo: 'I',
        logoClass: 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100',
        count: 1,
    },
    {
        name: 'Flower Analytics',
        logo: '✿',
        logoClass: 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
        count: null,
    },
    {
        name: 'Kiwi CRM',
        logo: 'K',
        logoClass: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300',
        count: 5,
    },
];
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
                    <span class="inline-flex items-center gap-2"><Icon name="connection" class="size-4! opacity-60! text-gray-925 dark:text-white" aria-hidden="true" />{{ __('Connect') }}</span>
                    <Icon name="chevron-right" class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <span>{{ __('Email Notifications') }}</span>
                </Heading>
            </PanelHeader>
            <Card>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <div
                        v-for="integration in integrations"
                        :key="integration.name"
                        class="space-y-2"
                    >
                        <a
                            href="#"
                            class="block mb-2 aspect-square rounded-lg border border-gray-300 bg-gray-50/30 p-4 dark:border-gray-700 dark:bg-gray-950/40 dark:hover:bg-gray-900"
                        >
                            <span
                                class="inline-flex h-14 w-14 items-center justify-center rounded-xl text-2xl font-bold"
                                :class="integration.logoClass"
                            >
                                {{ integration.logo }}
                            </span>
                        </a>
                        <div class="flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <span
                                class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded text-2xs font-bold"
                                :class="integration.logoClass"
                                aria-hidden="true"
                            >
                                {{ integration.logo }}
                            </span>
                            <span class="truncate">{{ __(integration.name) }}</span>
                            <Badge
                                v-if="integration.count"
                                size="sm"
                                color="white"
                                pill
                            >
                                {{ integration.count }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </Card>
        </Panel>
    </div>
</template>
