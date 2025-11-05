<script setup>
import { computed, ref, watch } from 'vue';
import { startRegistration, browserSupportsWebAuthn } from '@simplewebauthn/browser';
import { router } from '@inertiajs/vue3';
import axios from 'axios'
import Head from '@/pages/layout/Head.vue';
import { Header, Button, EmptyStateMenu, EmptyStateItem, Listing, DropdownItem, Modal, Input, ModalClose, Field } from '@ui';
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue';
import { toggleArchitecturalBackground } from '@/pages/layout/architectural-background.js';

const props = defineProps([
    'passkeys',
    'createUrl',
    'storeUrl',
    'deleteUrl',
])

watch(
    () => props.passkeys,
    (passkeys) => toggleArchitecturalBackground(passkeys.length === 0),
    { immediate: true }
);

const error = ref(null);
const showCreateModal = ref(false);
const showErrorModal = computed(() => !!error.value);
const passkeyWaiting = ref(false);
const passkeyName = ref('');

watch(showCreateModal, (opened) => {
    if (!opened) passkeyName.value = '';
});

const columns = [
    { label: __('Name'), field: 'name' },
    { label: __('Last Login'), field: 'last_login' },
];

function deletePasskey(passkey) {
    if (confirm(__('Are you sure?'))) {
        axios.delete(passkey.delete_url).then(() => router.reload());
    }
}

async function createPasskey() {
    if (! browserSupportsWebAuthn()) {
        alert(__('statamic::messages.passkeys_browser_unsupported'));
        return;
    }

    passkeyWaiting.value = true;
    const name = passkeyName.value || `${__('Passkey')} ${props.passkeys.length + 1}`;
    showCreateModal.value = false;
    const authOptionsResponse = await fetch(props.createUrl);

    let startRegistrationResponse;
    try {
        startRegistrationResponse = await startRegistration(await authOptionsResponse.json());
    } catch (e) {
        console.error(e);
        passkeyWaiting.value = false;
        return;
    }

    axios.post(props.storeUrl, { ...startRegistrationResponse, name })
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
                @click="showCreateModal = true"
                :disabled="passkeyWaiting"
                :loading="passkeyWaiting"
            />
        </template>
    </Header>

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
                @click="deletePasskey(passkey)"
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
            />
        </template>
    </Listing>

    <EmptyStateMenu v-else :heading="__('statamic::messages.passkeys_configure_intro')">
        <EmptyStateItem
            @click="showCreateModal = true"
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

    <Modal
        :title="__('Create a Passkey')"
        v-model:open="showCreateModal"
    >
        <Field :label="__('Name')">
            <Input
                v-model="passkeyName"
                @keyup.enter="createPasskey"
            />
        </Field>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose>
                    <Button variant="ghost" :text="__('Cancel')" />
                </ModalClose>
                <Button
                    variant="primary"
                    :text="__('Create')"
                    @click="createPasskey"
                />
            </div>
        </template>
    </Modal>

</template>
