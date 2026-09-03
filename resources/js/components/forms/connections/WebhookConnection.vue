<script setup lang="ts">
import { ref } from 'vue';
import { Badge, Button, Field, Icon, Label, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import ConnectionRows from './ConnectionRows.vue';
import ConnectionRules, { conditionsSummary } from './ConnectionRules.vue';

defineEmits(['update:modelValue']);

const props = defineProps({
    form: Object,
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    blueprint: Object,
    meta: { type: Object, default: () => ({}) },
    defaults: Object,
    examplePayload: String,
});

const showExamplePayload = ref<boolean>(props.modelValue.length === 0);
</script>

<template>
    <Label v-if="modelValue.length" :text="__('Webhooks')" />

    <ConnectionRows
        :model-value="modelValue"
        :errors
        :defaults
        :add-label="__('Add Webhook')"
        :empty-heading="__('No webhooks yet')"
        :empty-description="__('statamic::messages.webhook_connection_empty_description')"
        :delete-heading="__('Delete Webhook')"
        :delete-description="__('statamic::messages.webhook_connection_delete_confirmation')"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header="{ item: webhook, collapsed }">
            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                <Icon name="globe-setting" class="size-3.5 me-1 opacity-100! text-purple-600 dark:text-purple-400" aria-hidden="true" />
                {{ webhook.url || __('New Webhook') }}
            </Badge>
            <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap gap-1.5!">
                <span class="truncate">{{ conditionsSummary(webhook.conditions) }}</span>
            </Subheading>
        </template>

        <template #default="{ item: webhook, errors }">
            <ConnectionRules
                v-model:conditions="webhook.conditions"
                :always-label="__('Always send')"
                :if-label="__('Send if...')"
            >
                <template #then>
                    <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <PublishContainer
                            :errors
                            :blueprint
                            :model-value="webhook"
                            :meta="meta[webhook.id] ?? defaults.meta"
                            :name="`webhook-connection-${webhook.id}`"
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

    <Field
        class="mt-8"
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
</template>
