<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import { nanoid as uniqid } from 'nanoid';
import { keys } from '@api';
import { Badge, Button, Field, Icon, Input, Label, Switch } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import LogicEmptyState from '@/components/forms/logic/LogicEmptyState.vue';
import ConnectionConfig from './ConnectionConfig.vue';

const props = defineProps({
    form: Object,
    config: { type: Array, default: () => [] },
    action: String,
    examplePayload: Object,
});

const dirtyKey = 'webhook-connection';
const sortableItemClass = 'connection-config-item';
const sortableHandleClass = 'connection-config-handle';

const errors = ref({});
const saving = ref(false);
const saveBinding = ref(null);

const webhooks = ref(props.config.map((webhook) => ({
    enabled: true,
    verify_ssl: true,
    ...webhook,
})));

const examplePayload = computed(() => JSON.stringify(props.examplePayload, null, 2));

const collapsed = ref(webhooks.value.map((webhook) => webhook.id));

const collapse = (id) => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const expand = (id) => (collapsed.value = collapsed.value.filter((webhookId) => webhookId !== id));

const addWebhook = () => webhooks.value.push({ id: uniqid(), enabled: true, verify_ssl: true });

const duplicateWebhook = (id) => {
    const index = webhooks.value.findIndex((webhook) => webhook.id === id);

    webhooks.value.splice(index + 1, 0, { ...webhooks.value[index], id: uniqid() });
};

const removeWebhook = (id) => {
    webhooks.value = webhooks.value.filter((webhook) => webhook.id !== id);
    collapsed.value = collapsed.value.filter((webhookId) => webhookId !== id);
};

const error = (index, handle) => errors.value[`configs.${index}.${handle}`]?.[0];
const hasError = (index) => Object.keys(errors.value).some((key) => key.startsWith(`configs.${index}.`));

const save = () => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, { configs: webhooks.value })
        .then(() => {
            Statamic.$dirty.remove(dirtyKey);
            Statamic.$toast.success(__('Saved'));
        })
        .catch((e) => {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
                Statamic.$toast.error(e.response.data.message);
            } else {
                Statamic.$toast.error(__('Something went wrong'));
            }
        })
        .finally(() => (saving.value = false));
};

watch(webhooks, () => Statamic.$dirty.add(dirtyKey), { deep: true });

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => {
    Statamic.$dirty.remove(dirtyKey);
    saveBinding.value?.destroy();
});
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" :disabled="saving" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <Field
        class="mb-8"
        :label="__('Request Format')"
        :instructions="__('statamic::messages.webhook_connection_payload_instructions')"
    >
        <pre class="mt-2 overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-800 dark:border-white/10 dark:bg-gray-950/40 dark:text-gray-300"><code>{{ examplePayload }}</code></pre>
    </Field>

    <Label :text="__('Webhooks')" />

    <LogicEmptyState
        v-if="webhooks.length === 0"
        :heading="__('No webhooks yet')"
        :description="__('statamic::messages.webhook_connection_description')"
    >
        <Button size="sm" :text="__('Add Webhook')" icon="plus" @click="addWebhook" />
    </LogicEmptyState>

    <template v-else>
        <SortableList
            v-model="webhooks"
            vertical
            constrain-dimensions
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
        >
            <div class="relative space-y-6 mb-0" data-connection-list>
                <div v-for="(webhook, index) in webhooks" :key="webhook.id" :class="sortableItemClass">
                    <ConnectionConfig
                        :collapsed="collapsed.includes(webhook.id)"
                        :enabled="webhook.enabled !== false"
                        :has-error="hasError(index)"
                        :handle-class="sortableHandleClass"
                        @collapsed="collapse(webhook.id)"
                        @expanded="expand(webhook.id)"
                        @duplicated="duplicateWebhook(webhook.id)"
                        @removed="removeWebhook(webhook.id)"
                        @update:enabled="webhook.enabled = $event"
                    >
                        <template #title>
                            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                                <Icon name="globe-arrow" class="size-3.5 me-1 opacity-100! text-teal-600 dark:text-teal-400" aria-hidden="true" />
                                {{ webhook.url || __('New Webhook') }}
                            </Badge>
                        </template>

                        <div class="space-y-6">
                            <Field
                                :label="__('URL')"
                                :instructions="__('statamic::messages.webhook_connection_url_instructions')"
                                required
                                :error="error(index, 'url')"
                            >
                                <Input v-model="webhook.url" type="url" />
                            </Field>
                            <Field
                                :label="__('Verify SSL Certificate')"
                                :instructions="__('statamic::messages.webhook_connection_verify_ssl_instructions')"
                            >
                                <Switch v-model="webhook.verify_ssl" />
                            </Field>
                        </div>
                    </ConnectionConfig>
                </div>
            </div>
        </SortableList>

        <div class="inline-flex relative pt-6">
            <div class="absolute inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-gray-850" />
            <Button size="sm" :text="__('Add Webhook')" icon="plus" class="relative" @click="addWebhook" />
        </div>
    </template>
</template>

<style scoped>
[data-connection-list]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-connection-list]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
