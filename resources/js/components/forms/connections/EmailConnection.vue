<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { keys } from '@api';
import { usePage } from '@inertiajs/vue3';
import { Badge, Button, Icon, PublishContainer, PublishFields, PublishFieldsProvider, Subheading } from '@ui';
import ConnectionRows, { connectionRows } from './ConnectionRows.vue';
import ConnectionRules, { conditionsSummary } from './ConnectionRules.vue';

interface Email {
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
    emails: Object,
    defaults: Object,
});

const suggestableFields = usePage().props.suggestableFields;

const errors = ref<object>({});
const saving = ref<boolean>(false);
const saveBinding = ref<ReturnType<typeof keys.bindGlobal> | null>(null);
const emails = ref<Email[]>(connectionRows(props.config, props.emails));

const recipients = (to: string[] | string): string =>
    [to].flat().map((recipient) => {
        if (!recipient.startsWith('field:')) return recipient;

        const handle = recipient.replace('field:', '');
        const field = suggestableFields.find((field) => field.handle === handle);

        return __(field?.config?.display ?? handle);
    }).join(', ');

const hasError = (index: number) => Object.keys(errors.value).some((key) => key.startsWith(`emails.${index}.`));

const rowErrors = (index: number) =>
    Object.entries(errors.value)
        .filter(([key]) => key.startsWith(`emails.${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`emails.${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});

const save = (): void => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, {
        emails: emails.value.map(({ values, meta, ...config }) => ({ ...config, ...values })),
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
                {{ email.values.to?.length ? __('Message sent to :email', { email: recipients(email.values.to) }) : __('New Email') }}
            </Badge>
            <Subheading v-show="collapsed" class="overflow-hidden text-ellipsis whitespace-nowrap gap-1.5!">
                <span class="truncate">{{ conditionsSummary(email.conditions) ?? email.values.subject }}</span>
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
                            v-model="email.values"
                            :meta="email.meta"
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
