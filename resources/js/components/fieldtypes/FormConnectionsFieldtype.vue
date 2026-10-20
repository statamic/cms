<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Badge, Button, Card, Icon, Stack, ToggleGroup, ToggleItem } from '@/components/ui';
import ConnectorsListing, { View } from '@/components/forms/connections/ConnectorsListing.vue';
import ConnectionEditor from '@/components/forms/connections/ConnectionEditor.vue';
import clone from '@/util/clone.js';
import { preferences, toast } from '@api';
import { __ } from '@/bootstrap/globals';

type ConnectorType = {
    handle: string;
    title: string;
    description: string;
    icon: string;
    developer: string;
    action: string;
    editor: string;
}

type ConnectorComponent = {
    name: string;
    props: Record<string, unknown>;
}

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);

const open = ref(false);
const editingHandle = ref<string | null>(null);
const draft = ref<unknown[] | Record<string, unknown> | null>(null);
const component = ref<ConnectorComponent | null>(null);
const loading = ref(false);
const errors = ref<Record<string, string[]>>({});
const saving = ref(false);

const view = ref<View>(preferences.get('forms.connect.view', View.Grid));

watch(view, (view: View) => preferences.set('forms.connect.view', view));

const form = computed(() => props.meta.form ?? {});
const types = computed<ConnectorType[]>(() => props.meta.types ?? []);

const connectors = computed(() => {
    return types.value.map((type) => ({
        ...type,
        count: (props.value?.[type.handle] ?? []).length,
    }));
});

const totalConfigured = computed(() => connectors.value.reduce((total, c) => total + c.count, 0));

const connector = computed(() => types.value.find((type) => type.handle === editingHandle.value));

let editorRequest = 0;

function selectConnector(handle: string) {
    const request = ++editorRequest;

    errors.value = {};
    editingHandle.value = handle;
    loading.value = true;

    axios.post(connector.value.editor, clone(props.value?.[handle] ?? []))
        .then(({ data }) => {
            if (request !== editorRequest) return;

            draft.value = data.value;
            component.value = data.component;
        })
        .catch(() => {
            if (request !== editorRequest) return;

            toast.error(__('Something went wrong'));
            closeEditor();
        })
        .finally(() => {
            if (request === editorRequest) loading.value = false;
        });
}

function closeEditor() {
    editorRequest++;
    editingHandle.value = null;
    draft.value = null;
    component.value = null;
    loading.value = false;
    errors.value = {};
    saving.value = false;
}

function applyConnector() {
    if (saving.value || loading.value || !connector.value) return;

    const request = editorRequest;
    const handle = editingHandle.value;

    errors.value = {};
    saving.value = true;

    axios.post(connector.value.action, draft.value)
        .then(({ data }) => {
            if (request !== editorRequest) return;

            update({ ...(props.value ?? {}), [handle]: data });
            closeEditor();
        })
        .catch((e) => {
            if (request !== editorRequest) return;

            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
            }
        })
        .finally(() => {
            if (request === editorRequest) saving.value = false;
        });
}

defineExpose(expose);
</script>

<template>
    <div class="flex justify-end">
        <Button icon="connection" @click="open = true">
            {{ __('Configure Connections') }}
            <Badge v-if="totalConfigured" size="sm" pill class="ms-1.5">{{ totalConfigured }}</Badge>
        </Button>
    </div>

    <Stack v-model:open="open" size="half" :title="__('Connections')">
        <template #header-actions>
            <ToggleGroup v-model="view">
                <ToggleItem :value="View.Grid" icon="layout-grid" />
                <ToggleItem :value="View.List" icon="layout-list" />
            </ToggleGroup>
        </template>

        <Card :class="{ 'p-0!': view === View.List }">
            <ConnectorsListing :connectors :linkable="false" :view @select="selectConnector" />
        </Card>

        <template #footer-end>
            <Button variant="primary" :text="__('Done')" @click="open = false" />
        </template>
    </Stack>

    <Stack
        :open="!!editingHandle"
        size="half"
        :title="connector ? __(connector.title) : ''"
        :before-close="() => !saving"
        @update:open="closeEditor"
    >
        <div v-if="loading" class="flex justify-center py-8">
            <Icon name="loading" />
        </div>
        <ConnectionEditor v-else-if="component" :form :component :errors v-model="draft" />

        <template #footer-end>
            <Button variant="ghost" :text="__('Cancel')" :disabled="saving" @click="closeEditor" />
            <Button variant="primary" :text="__('Apply')" :loading="saving" :disabled="loading" @click="applyConnector" />
        </template>
    </Stack>
</template>
