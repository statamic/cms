<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Badge, Icon, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import ConnectionRows from './ConnectionRows.vue';
import ConnectionRules, { conditionsSummary } from './ConnectionRules.vue';

interface Email {
    id: string;
    enabled: boolean;
    conditions: { _id: string; field: string; operator: string; value: string }[];
    [field: string]: unknown;
}

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    form: Object,
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    blueprint: Object,
    meta: { type: Object, default: () => ({}) },
    defaults: Object,
});

const suggestableFields = usePage().props.suggestableFields;

const emails = computed({
    get: () => props.modelValue as Email[],
    set: (value: Email[]) => emit('update:modelValue', value),
});

const recipients = (to: string[] | string): string =>
    [to].flat().map((recipient) => {
        if (!recipient.startsWith('field:')) return recipient;

        const handle = recipient.replace('field:', '');
        const field = suggestableFields.find((field) => field.handle === handle);

        return __(field?.config?.display ?? handle);
    }).join(', ');

const hasError = (index: number) => Object.keys(props.errors).some((key) => key.startsWith(`${index}.`));

const rowErrors = (index: number) =>
    Object.entries(props.errors)
        .filter(([key]) => key.startsWith(`${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});
</script>

<template>
    <ConnectionRows
        v-model="emails"
        :defaults
        :has-error
        :add-label="__('Add Email')"
        :empty-heading="__('No emails yet')"
        :empty-description="__('statamic::messages.email_connection_empty_description')"
        :delete-heading="__('Delete Email')"
        :delete-description="__('statamic::messages.email_connection_delete_confirmation')"
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

        <template #default="{ item: email, index }">
            <ConnectionRules
                v-model:conditions="email.conditions"
                :always-label="__('Always send')"
                :if-label="__('Send if...')"
            >
                <template #then>
                    <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <PublishContainer
                            :name="`email-connection-${email.id}`"
                            :blueprint="blueprint"
                            v-model="emails[index]"
                            :meta="meta[email.id] ?? defaults.meta"
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
