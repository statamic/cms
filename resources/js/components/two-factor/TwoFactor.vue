<script setup>
import { ref, watch } from 'vue';
import ConfirmationModal from '@statamic/components/modals/ConfirmationModal.vue';
import TwoFactorSetup from './Setup.vue';
import TwoFactorRecoveryCodesModal from './RecoveryCodesModal.vue';
import { requireElevatedSession } from '@statamic/components/elevated-sessions';
import { Popover, Button } from '@statamic/ui';
import axios from 'axios';

const props = defineProps(['wasSetup', 'isEnforced', 'routes']);

const loading = ref(false);
const recoveryCodesModalOpen = ref(false);
const setupModalOpen = ref(false);
const disableModalOpen = ref(false);
const isSetup = ref(props.wasSetup);
const popoverOpen = ref(false);

watch(loading, (loading) => {
    Statamic.$progress.loading(loading);
});

function openSetupModal() {
    popoverOpen.value = false;

    requireElevatedSession()
        .then(() => (setupModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function openRecoveryCodesModal() {
    popoverOpen.value = false;

    requireElevatedSession()
        .then(() => (recoveryCodesModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function openDisableModal() {
    popoverOpen.value = false;

    requireElevatedSession()
        .then(() => (disableModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function setupComplete() {
    isSetup.value = true;
    setupModalOpen.value = false;
}

function disable() {
    loading.value = true;

    axios
        .delete(props.routes.disable)
        .then((response) => {
            isSetup.value = false;
            disableModalOpen.value = false;
            Statamic.$toast.success(__('Disabled two factor authentication'));
            if (response.data.redirect) window.location = response.data.redirect;
        })
        .catch((error) => Statamic.$toast.error(error.message))
        .finally(() => {
            loading.value = false;
            disableModalOpen.value = false;
        });
}
</script>

<template>
    <template v-if="!isSetup">
        <div>
            <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

            <div class="flex space-x-2">
                <Button @click="openSetupModal">
                    {{ __('Enable two factor authentication') }}
                </Button>
            </div>
        </div>
    </template>

    <template v-else>
        <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enabled') }}</p>

        <div class="flex items-center space-x-4">
            <Button @click="openRecoveryCodesModal">
                {{ __('Show recovery codes') }}
            </Button>

            <Button variant="danger" @click="openDisableModal">
                {{ __('Disable two factor authentication') }}
            </Button>
        </div>
    </template>

    <TwoFactorSetup
        v-if="setupModalOpen"
        :enable-url="routes.enable"
        :recovery-code-urls="routes.recoveryCodes"
        @close="setupModalOpen = false"
        @setup-complete="setupComplete"
    />

    <TwoFactorRecoveryCodesModal
        v-if="recoveryCodesModalOpen"
        :recovery-codes-url="routes.recoveryCodes.show"
        :generate-url="routes.recoveryCodes.generate"
        :download-url="routes.recoveryCodes.download"
        @close="recoveryCodesModalOpen = false"
    />

    <ConfirmationModal
        v-if="disableModalOpen"
        :title="__('Are you sure?')"
        :danger="true"
        @confirm="disable"
        @cancel="disableModalOpen = false"
    >
        <p class="mb-2" v-html="__('statamic::messages.disable_two_factor_authentication')"></p>

        <p
            v-html="
                isEnforced
                    ? __('statamic::messages.disable_two_factor_authentication_current_user_enforced')
                    : __('statamic::messages.disable_two_factor_authentication_current_user_optional')
            "
        ></p>
    </ConfirmationModal>
</template>
