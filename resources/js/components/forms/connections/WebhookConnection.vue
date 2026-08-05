<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import { nanoid as uniqid } from 'nanoid';
import { keys } from '@api';
import { Badge, Button, Field, Icon, Label, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import { deepClone } from '@/util/clone.js';
import ConnectionList from './ConnectionList.vue';
import ConnectionLogic, { conditionsSummary } from './ConnectionLogic.vue';

const props = defineProps({
    form: Object,
    config: { type: Array, default: () => [] },
    action: String,
    blueprint: Object,
    rows: { type: Array, default: () => [] },
    defaults: Object,
    examplePayload: Object,
});

const dirtyKey = 'webhook-connection';

const errors = ref({});
const saving = ref(false);
const saveBinding = ref(null);

const webhooks = ref(props.config.map((config, index) => ({
    id: config.id ?? config._id,
    enabled: config.enabled ?? true,
    conditions: (config.conditions ?? []).map((condition) => ({ ...condition, _id: uniqid() })),
    values: props.rows[index]?.values ?? deepClone(props.defaults.values),
    meta: props.rows[index]?.meta ?? deepClone(props.defaults.meta),
})));

const examplePayload = computed(() => JSON.stringify(props.examplePayload, null, 2));

const addWebhook = () => webhooks.value.push({
    id: uniqid(),
    enabled: true,
    conditions: [],
    values: deepClone(props.defaults.values),
    meta: deepClone(props.defaults.meta),
});

const duplicateWebhook = (webhook) => {
    const index = webhooks.value.indexOf(webhook);

    webhooks.value.splice(index + 1, 0, {
        id: uniqid(),
        enabled: webhook.enabled,
        conditions: webhook.conditions.map((condition) => ({ ...condition, _id: uniqid() })),
        values: deepClone(webhook.values),
        meta: deepClone(webhook.meta),
    });
};

const removeWebhook = (webhook) => (webhooks.value = webhooks.value.filter((item) => item !== webhook));

const hasError = (index) => Object.keys(errors.value).some((key) => key.startsWith(`webhooks.${index}.`));

const rowErrors = (index) =>
    Object.entries(errors.value)
        .filter(([key]) => key.startsWith(`webhooks.${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`webhooks.${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});

const save = () => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, {
        webhooks: webhooks.value.map(({ values, meta, ...config }) => ({ ...config, ...values })),
    })
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
    saveBinding.value = keys.bindGlobal(['mod+s'], (e) => {
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

    <ConnectionList
        v-model="webhooks"
        :add-label="__('Add Webhook')"
        :empty-heading="__('No webhooks yet')"
        :empty-description="__('statamic::messages.webhook_connection_description')"
        :delete-heading="__('Delete Webhook')"
        :delete-description="__('statamic::messages.webhook_connection_delete_confirmation')"
        :has-error
        @add="addWebhook"
        @duplicate="duplicateWebhook"
        @remove="removeWebhook"
    >
        <template #header="{ item: webhook, collapsed }">
            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                <Icon name="globe-arrow" class="size-3.5 me-1 opacity-100! text-teal-600 dark:text-teal-400" aria-hidden="true" />
                {{ webhook.values.url || __('New Webhook') }}
            </Badge>
            <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap gap-1.5!">
                <span class="truncate">{{ conditionsSummary(webhook.conditions) }}</span>
            </Subheading>
        </template>

        <template #default="{ item: webhook, index }">
            <ConnectionLogic
                v-model:conditions="webhook.conditions"
                :always-label="__('Always send')"
                :if-label="__('Send if...')"
            >
                <template #then>
                    <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <PublishContainer
                            :name="`webhook-connection-${webhook.id}`"
                            :blueprint="blueprint"
                            v-model="webhook.values"
                            :meta="webhook.meta"
                            :errors="rowErrors(index)"
                        >
                            <PublishFieldsProvider :fields="blueprint.tabs[0].sections[0].fields">
                                <PublishFields />
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </template>
            </ConnectionLogic>
        </template>
    </ConnectionList>
</template>
