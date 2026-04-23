<script setup>
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';
import { Button, Card, Header, Heading, Icon, Panel, PanelHeader, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import { computed, ref } from 'vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps({
    form: Object,
});

const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const logicView = ref('list');
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="py-4 mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
            <template #actions>
                <ToggleGroup v-model="logicView" size="xs">
                    <ToggleItem value="list" icon="layout-list" :label="__('List')" />
                    <ToggleItem value="tree" icon="logic-tree" :label="__('Tree')" />
                </ToggleGroup>
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
