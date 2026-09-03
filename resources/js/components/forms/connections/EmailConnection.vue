<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Badge, Icon, Label, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import ConnectionRows from './ConnectionRows.vue';
import ConnectionRules, { conditionsSummary } from './ConnectionRules.vue';
import { __n } from '@/bootstrap/globals';

defineEmits(['update:modelValue']);

defineProps({
    form: Object,
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    blueprint: Object,
    meta: { type: Object, default: () => ({}) },
    defaults: Object,
});

const suggestableFields = usePage().props.suggestableFields;

const recipients = (to: string[] | string): string =>
    [to].flat().map((recipient) => {
        if (!recipient.startsWith('field:')) return recipient;

        const handle = recipient.replace('field:', '');
        const field = suggestableFields.find((field) => field.handle === handle);

        return __(field?.config?.display ?? handle);
    }).join(', ');
</script>

<template>
    <Label v-if="modelValue.length">
        <span class="inline-flex items-center gap-2">
            <Badge size="lg" pill color="white">{{ modelValue.length }}</Badge>
            <span>{{ __n(':count Email Configured|:count Emails Configured', modelValue.length) }}</span>
        </span>
    </Label>

    <ConnectionRows
        :model-value="modelValue"
        :errors
        :defaults
        :add-label="__('Add Email')"
        :empty-heading="__('No emails yet')"
        :empty-description="__('statamic::messages.email_connection_empty_description')"
        :delete-heading="__('Delete Email')"
        :delete-description="__('statamic::messages.email_connection_delete_confirmation')"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header="{ item: email, collapsed }">
            <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                <Icon name="mail-sign-at" class="size-3.5 me-1 opacity-100! text-blue-600 dark:text-blue-400" aria-hidden="true" />
                {{ email.to?.length ? __('Message sent to :email', { email: recipients(email.to) }) : __('New Email') }}
            </Badge>
            <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap gap-1.5!">
                <span class="truncate">{{ conditionsSummary(email.conditions) ?? email.subject }}</span>
            </Subheading>
        </template>

        <template #default="{ item: email, errors }">
            <ConnectionRules
                v-model:conditions="email.conditions"
                :always-label="__('Always send')"
                :if-label="__('Send if...')"
            >
                <template #then>
                    <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <PublishContainer
                            :errors
                            :blueprint
                            :model-value="email"
                            :meta="meta[email.id] ?? defaults.meta"
                            :name="`email-connection-${email.id}`"
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
