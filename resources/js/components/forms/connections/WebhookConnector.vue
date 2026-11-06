<script setup lang="ts">
import { ref } from 'vue';
import { Badge, Button, Field, Icon, Label } from '@ui';
import ConnectionList from './ConnectionList.vue';
import ConnectionSummary from './ConnectionSummary.vue';

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
    <Label :text="__('Webhooks')" class="mb-2" />

    <ConnectionList
        :model-value="modelValue"
        :errors
        :defaults
        :blueprint
        :meta
        name="webhook-connection"
        :add-label="__('Add Webhook')"
        :description="__('statamic::messages.webhook_connection_instructions')"
        :delete-heading="__('Delete Webhook')"
        :delete-description="__('statamic::messages.webhook_connection_delete_confirmation')"
        :always-label="__('Always send')"
        :if-label="__('Send if...')"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header="{ item: webhook, collapsed }">
            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                <Icon name="globe-setting" class="size-3.5 me-1 opacity-100! text-purple-600 dark:text-purple-400" aria-hidden="true" />
                {{ webhook.url || __('New Webhook') }}
            </Badge>
            <ConnectionSummary
                v-show="collapsed"
                :conditions="webhook.conditions"
            />
        </template>
    </ConnectionList>

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
