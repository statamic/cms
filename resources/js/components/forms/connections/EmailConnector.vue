<script setup lang="ts">
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Badge, Button, Icon, Label } from '@ui';
import ConnectionList from './ConnectionList.vue';
import ConnectionSummary from './ConnectionSummary.vue';
import EmailPreview from './EmailPreview.vue';

defineEmits(['update:modelValue']);

defineProps({
    form: Object,
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    blueprint: Object,
    meta: { type: Object, default: () => ({}) },
    defaults: Object,
    previewUrl: String,
});

const suggestableFields = usePage().props.suggestableFields ?? [];

const previewing = ref<Record<string, unknown> | null>(null);

const recipients = (to: string[] | string): string =>
    [to].flat().map((recipient) => {
        if (!recipient.startsWith('field:')) return recipient;

        const handle = recipient.replace('field:', '');
        const field = suggestableFields.find((field) => field.handle === handle);

        return __(field?.config?.display ?? handle);
    }).join(', ');
</script>

<template>
    <Label :text="__('Emails')" class="mb-2" />

    <ConnectionList
        :model-value="modelValue"
        :errors
        :defaults
        :blueprint
        :meta
        name="email-connection"
        :add-label="__('Add Email')"
        :description="__('statamic::messages.email_connection_instructions')"
        :delete-heading="__('Delete Email')"
        :delete-description="__('statamic::messages.email_connection_delete_confirmation')"
        :always-label="__('Always send')"
        :if-label="__('Send if...')"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header="{ item: email, collapsed }">
            <div class="flex items-center justify-between w-full gap-2 me-1">
                <div class="flex items-center gap-1.75">
                    <Badge size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
                        <Icon name="mail-sign-at" class="size-3.5 me-1 opacity-100! text-blue-600 dark:text-blue-400" aria-hidden="true" />
                        {{ email.to?.length ? __('Message sent to :email', { email: recipients(email.to) }) : __('New Email') }}
                    </Badge>
                    <ConnectionSummary
                        v-show="collapsed"
                        :conditions="email.conditions"
                        :fallback="email.subject"
                    />
                </div>

                <Button icon="eye" size="xs" :text="__('Preview')" @click.stop="previewing = email" />
            </div>
        </template>
    </ConnectionList>

    <EmailPreview v-model="previewing" :url="previewUrl" />
</template>
