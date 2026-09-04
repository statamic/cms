<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Badge, Button, Card, Stack, ToggleGroup, ToggleItem } from '@/components/ui';
import ConnectionsListing, { View } from '@/components/forms/connections/ConnectionsListing.vue';
import ConnectionEditor from '@/components/forms/connections/ConnectionEditor.vue';
import clone from '@/util/clone.js';
import { preferences } from '@api';

type ConnectionType = {
    handle: string;
    title: string;
    description: string;
    icon: string;
    developer: string;
    count: number | null;
    action: string;
}

type ConnectionComponent = {
    name: string;
    props: Record<string, unknown>;
}

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);

const open = ref(false);
const editingHandle = ref<string | null>(null);
const draft = ref<unknown[] | Record<string, unknown> | null>(null);
const errors = ref<Record<string, string[]>>({});
const saving = ref(false);

const view = ref<View>(preferences.get('forms.connect.view', View.Grid));

watch(view, (view: View) => preferences.set('forms.connect.view', view));

const form = computed(() => props.meta.form ?? {});
const types = computed<ConnectionType[]>(() => props.meta.types ?? []);
const components = computed<Record<string, ConnectionComponent>>(() => props.meta.components ?? {});

const connections = computed(() => {
    return types.value.map((type) => ({
        ...type,
        count: (props.value?.[type.handle] ?? []).length,
    }));
});

const totalConfigured = computed(() => connections.value.reduce((total, c) => total + c.count, 0));

const connection = computed(() => types.value.find((type) => type.handle === editingHandle.value));
const component = computed(() => editingHandle.value ? components.value[editingHandle.value] : null);

function selectConnection(handle: string) {
    draft.value = clone(props.value?.[handle] ?? []);
    errors.value = {};
    editingHandle.value = handle;
}

function closeEditor() {
    editingHandle.value = null;
    draft.value = null;
    errors.value = {};
}

function applyConnection() {
    if (saving.value || !connection.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(`${connection.value.action}?_save=false`, draft.value)
        .then(({ data }) => {
            update({ ...(props.value ?? {}), [editingHandle.value]: data });
            closeEditor();
        })
        .catch((e) => {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
            }
        })
        .finally(() => (saving.value = false));
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
            <ConnectionsListing :connections :linkable="false" :view @select="selectConnection" />
        </Card>

        <template #footer-end>
            <Button variant="primary" :text="__('Done')" @click="open = false" />
        </template>
    </Stack>

    <Stack :open="!!editingHandle" size="half" :title="connection ? __(connection.title) : ''" @update:open="closeEditor">
        <ConnectionEditor v-if="component" :form :component :errors v-model="draft" />

        <template #footer-end>
            <Button variant="ghost" :text="__('Cancel')" :disabled="saving" @click="closeEditor" />
            <Button variant="primary" :text="__('Apply')" :loading="saving" @click="applyConnection" />
        </template>
    </Stack>
</template>
