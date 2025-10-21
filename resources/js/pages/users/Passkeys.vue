<script setup>
import { computed, ref, watch } from 'vue';
import { startRegistration, browserSupportsWebAuthn } from '@simplewebauthn/browser';
import { router } from '@inertiajs/vue3';
import axios from 'axios'
import Head from '@/pages/layout/Head.vue';
import { Header, Button, EmptyStateMenu, EmptyStateItem, Listing, DropdownItem } from '@ui';
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue';
import { toggleArchitecturalBackground } from '@/pages/layout/architectural-background.js';

const props = defineProps([
    'passkeys',
    'optionsUrl',
    'verifyUrl',
    'deleteUrl',
])

watch(
    () => props.passkeys,
    (passkeys) => toggleArchitecturalBackground(passkeys.length === 0),
    { immediate: true }
);

const error = ref(null);
const showErrorModal = computed(() => !!error.value);
const showWebAuthn = browserSupportsWebAuthn();
const passkeyWaiting = ref(false);

const columns = [
    { label: __('Name'), field: 'id' },
    { label: __('Last Login'), field: 'last_login' },
];

function deletePasskey(id) {
    if (confirm(__('Are you sure?'))) {
        axios.delete(props.deleteUrl + id).then(() => router.reload());
    }
}

async function createPasskey() {
    passkeyWaiting.value = true;
    const authOptionsResponse = await fetch(props.optionsUrl);

    let startRegistrationResponse;
    try {
        startRegistrationResponse = await startRegistration(await authOptionsResponse.json());
    } catch (e) {
        console.error(e);
        passkeyWaiting.value = false;
        return;
    }

    axios.post(props.verifyUrl, startRegistrationResponse)
        .then(response => {
            if (response && response.data.verified) {
                router.reload();
                return;
            }

            error.value = response.data.message;
        }).catch(e => handleAxiosError(e))
        .finally(() => passkeyWaiting.value = false);
}

function handleAxiosError(e) {
    if (e.response) {
        const { message, errors } = e.response.data;
        error.value = message;
        return;
    }

    error.value = __('Something went wrong');
}
</script>

<template>

    <Head :title="__('Passkeys')" />

    <Header :title="__('Passkeys')" icon="key">
        <template #actions>
            <Button
                variant="primary"
                :text="__('Create Passkey')"
                @click="createPasskey"
                :disabled="passkeyWaiting"
                :loading="passkeyWaiting"
            />
        </template>
    </Header>

    <div v-if="!showWebAuthn">
        {{ __('statamic::messages.passkeys_browser_unsupported') }}
    </div>

    <Listing
        v-if="passkeys.length"
        :items="passkeys"
        :columns
        :allow-search="false"
        :allow-customizing-columns="false"
    >
        <template #cell-last_login="{ value }">
            <date-time v-if="value" :of="value" :options="{ relative: true }" />
            <template v-else>{{ __('Never') }}</template>
        </template>
        <template #prepended-row-actions="{ row: passkey }">
            <DropdownItem
                @click="deletePasskey(passkey.id)"
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
            />
        </template>
    </Listing>

    <EmptyStateMenu v-else :heading="__('statamic::messages.passkeys_configure_intro')">
        <EmptyStateItem
            @click="createPasskey"
            icon="key"
            :heading="__('Create a Passkey')"
            :description="__('Get started by creating your first passkey.')"
        />
    </EmptyStateMenu>

    <ConfirmationModal
        v-model:open="showErrorModal"
        :title="__('There was an error creating your passkey')"
        :body-text="error"
        :cancellable="false"
        :button-text="__('OK')"
    />


</template>
