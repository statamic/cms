<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { keys } from '@api';
import { Badge, Button, Field, Icon, Label, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import ConnectionRows, { connectionRows } from './ConnectionRows.vue';
import ConnectionRules, { conditionsSummary } from './ConnectionRules.vue';

interface Webhook {
    id: string;
    enabled: boolean;
    conditions: { _id: string; field: string; operator: string; value: string }[];
    values: object;
    meta: object;
}

const props = defineProps({
    form: Object,
    config: Array,
    action: String,
    blueprint: Object,
    webhooks: Object,
    defaults: Object,
    examplePayload: String,
});

const errors = ref<object>({});
const saving = ref<boolean>(false);
const saveBinding = ref<ReturnType<typeof keys.bindGlobal> | null>(null);
const showExamplePayload = ref<boolean>(props.config.length === 0);
const webhooks = ref<Webhook[]>(connectionRows(props.config, props.webhooks));

const hasError = (index: number) => Object.keys(errors.value).some((key) => key.startsWith(`webhooks.${index}.`));

const rowErrors = (index: number) =>
    Object.entries(errors.value)
        .filter(([key]) => key.startsWith(`webhooks.${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`webhooks.${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});

const save = (): void => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, {
        webhooks: webhooks.value.map(({ values, meta, ...config }) => ({ ...config, ...values })),
    })
        .then(() => {
            Statamic.$dirty.remove('connection');
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

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => saveBinding.value?.destroy());
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
        :label="__('Example Payload')"
        :instructions="__('statamic::messages.webhook_connection_payload_instructions')"
    >
        <template #actions>
            <Button
                variant="subtle"
                size="xs"
                :icon-append="showExamplePayload ? 'chevron-up' : 'chevron-down'"
                :text="showExamplePayload ? __('Hide') : __('Show')"
                :aria-expanded="showExamplePayload"
                @click="showExamplePayload = !showExamplePayload"
            />
        </template>

        <pre v-show="showExamplePayload" class="overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-800 dark:border-white/10 dark:bg-gray-950/40 dark:text-gray-300"><code>{{ examplePayload }}</code></pre>
    </Field>

    <Label v-if="webhooks.length" :text="__('Webhooks')" />

    <ConnectionRows
        v-model="webhooks"
        :defaults
        :has-error
        :add-label="__('Add Webhook')"
        :empty-heading="__('No webhooks yet')"
        :empty-description="__('statamic::messages.webhook_connection_empty_description')"
        :delete-heading="__('Delete Webhook')"
        :delete-description="__('statamic::messages.webhook_connection_delete_confirmation')"
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
            <ConnectionRules
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
                            :track-dirty-state="false"
                        >
                            <PublishFieldsProvider :fields="blueprint.tabs[0].sections[0].fields">
                                <PublishFields />
                            </PublishFieldsProvider>
                        </PublishContainer>
                    </div>
                </template>
            </ConnectionRules>
        </template>
    </ConnectionRows>
</template>
