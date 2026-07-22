<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import { nanoid as uniqid } from 'nanoid';
import { keys } from '@api';
import { Badge, Button, Field, Icon, Input, Label, Subheading, Switch } from '@ui';
import ConnectionList from './ConnectionList.vue';
import ConnectionLogic, { conditionsSummary } from './ConnectionLogic.vue';

const props = defineProps({
    form: Object,
    config: { type: Array, default: () => [] },
    action: String,
    examplePayload: Object,
});

const dirtyKey = 'webhook-connection';

const errors = ref({});
const saving = ref(false);
const saveBinding = ref(null);

const webhooks = ref(props.config.map((webhook) => ({
    enabled: true,
    verify_ssl: true,
    ...webhook,
    conditions: (webhook.conditions ?? []).map((condition) => ({ ...condition, _id: uniqid() })),
})));

const examplePayload = computed(() => JSON.stringify(props.examplePayload, null, 2));

const addWebhook = () => webhooks.value.push({ id: uniqid(), enabled: true, verify_ssl: true, conditions: [] });

const duplicateWebhook = (webhook) => {
    const index = webhooks.value.indexOf(webhook);

    webhooks.value.splice(index + 1, 0, {
        ...webhook,
        id: uniqid(),
        conditions: webhook.conditions.map((condition) => ({ ...condition, _id: uniqid() })),
    });
};

const removeWebhook = (webhook) => (webhooks.value = webhooks.value.filter((item) => item !== webhook));

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

    <ConnectionList
        v-model="webhooks"
        :add-label="__('Add Webhook')"
        :empty-heading="__('No webhooks yet')"
        :empty-description="__('statamic::messages.webhook_connection_description')"
        :has-error
        @add="addWebhook"
        @duplicate="duplicateWebhook"
        @remove="removeWebhook"
    >
        <template #header="{ item: webhook, collapsed }">
            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                <Icon name="globe-arrow" class="size-3.5 me-1 opacity-100! text-teal-600 dark:text-teal-400" aria-hidden="true" />
                {{ webhook.url || __('New Webhook') }}
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
                    <div class="space-y-6 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
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
                </template>
            </ConnectionLogic>
        </template>
    </ConnectionList>
</template>
