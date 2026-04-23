<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Card, Header, Heading, Panel, PanelHeader, StatusIndicator } from '@ui';
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
                <Heading :text="__('Section')" />
            </PanelHeader>
            <Card class="min-h-32"></Card>
        </Panel>
    </div>
</template>
