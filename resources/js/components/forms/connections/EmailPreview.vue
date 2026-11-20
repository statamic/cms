<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { Description, ErrorMessage, Icon, Stack } from '@ui';
import { __ } from '@/bootstrap/globals';

enum Format {
    Html = 'html',
    Text = 'text',
}

type Preview = {
    subject: string;
    from: string[];
    to: string[];
    cc: string[];
    bcc: string[];
    reply_to: string[];
    format: Format;
    body: string;
    attachments: boolean;
    sample: boolean;
};

const emit = defineEmits(['update:modelValue']);

const props = defineProps<{
    modelValue: Record<string, unknown> | null;
    url: string;
}>();

const preview = ref<Preview | null>(null);
const error = ref<string | null>(null);
const loading = ref<boolean>(false);

const envelope = computed(() =>
    [
        { label: __('Subject'), value: preview.value?.subject },
        { label: __('From'), value: preview.value?.from.join(', ') },
        { label: __('To'), value: preview.value?.to.join(', ') },
        { label: __('Cc'), value: preview.value?.cc.join(', ') },
        { label: __('Bcc'), value: preview.value?.bcc.join(', ') },
        { label: __('Reply To'), value: preview.value?.reply_to.join(', ') },
        { label: __('Attachments'), value: preview.value?.attachments && __('statamic::messages.email_connection_preview_attachments') },
    ].filter((row) => row.value),
);

const load = (email: Record<string, unknown>): void => {
    preview.value = null;
    error.value = null;
    loading.value = true;

    axios
        .post(props.url, email)
        .then((response) => (preview.value = response.data))
        .catch((e) => (error.value = e.response?.data?.message ?? __('Something went wrong')))
        .finally(() => (loading.value = false));
};

const close = (): void => emit('update:modelValue', null);

watch(
    () => props.modelValue,
    (email) => email && load(email),
    { immediate: true },
);
</script>

<template>
    <Stack :open="modelValue !== null" size="half" inset :title="__('Preview Email')" icon="mail" @update:open="$event || close()">
        <div class="flex h-full flex-col">
            <div class="space-y-3 border-b border-gray-300 px-6 py-4 dark:border-gray-700">
                <Description
                    :text="preview?.sample === false
                        ? __('statamic::messages.email_connection_preview_latest_instructions')
                        : __('statamic::messages.email_connection_preview_sample_instructions')"
                />
                <dl v-if="preview" class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                    <template v-for="row in envelope" :key="row.label">
                        <dt class="text-gray-600 dark:text-gray-400">{{ row.label }}</dt>
                        <dd class="break-words text-gray-900 dark:text-gray-100">{{ row.value }}</dd>
                    </template>
                </dl>
            </div>

            <div v-if="loading" class="flex flex-1 items-center justify-center">
                <Icon name="loading" />
            </div>
            <ErrorMessage v-else-if="error" class="p-6">{{ error }}</ErrorMessage>
            <iframe
                v-else-if="preview?.format === Format.Html"
                :srcdoc="preview.body"
                sandbox="allow-popups allow-popups-to-escape-sandbox"
                class="w-full flex-1 border-0 bg-white"
                :title="__('Preview Email')"
            />
            <pre v-else-if="preview" class="flex-1 overflow-auto whitespace-pre-wrap p-6 font-mono text-sm text-gray-900 dark:text-gray-100">{{ preview.body }}</pre>
        </div>
    </Stack>
</template>
