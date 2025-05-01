<script setup>
import { ref } from 'vue';
import DisableTwoFactor from './Disable.vue';
import TwoFactorSetup from './Setup.vue';
import TwoFactorRecoveryCodesModal from './RecoveryCodesModal.vue';
import { requireElevatedSession } from '@statamic/components/elevated-sessions';

const props = defineProps(['wasSetup', 'isEnforced', 'routes']);

const recoveryCodesModalOpen = ref(false);
const setupModalOpen = ref(false);
const isSetup = ref(props.wasSetup);

function openSetupModal() {
    requireElevatedSession()
        .then(() => (setupModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function openRecoveryCodesModal() {
    requireElevatedSession()
        .then(() => (recoveryCodesModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function setupComplete() {
    isSetup.value = true;
    setupModalOpen.value = false;
}

function resetComplete() {
    isSetup.value = false;
}
</script>

<template>
    <popover placement="bottom" ref="popper">
        <template #trigger>
            <button class="btn" v-text="__('Two Factor Authentication')" />
        </template>
        <div class="max-w-sm p-4">
            <template v-if="!isSetup">
                <div>
                    <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

                    <div class="flex space-x-2">
                        <button class="btn" @click="openSetupModal">
                            {{ __('Enable two factor authentication') }}
                        </button>
                    </div>
                </div>
            </template>

            <template v-else>
                <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enabled') }}</p>

                <div class="flex items-center space-x-4">
                    <button class="btn" @click="openRecoveryCodesModal">
                        {{ __('Show recovery codes') }}
                    </button>

                    <DisableTwoFactor
                        :url="routes.disable"
                        :is-enforced="isEnforced"
                        @reset-complete="resetComplete"
                        v-slot="{ confirm }"
                    >
                        <button class="btn-danger" @click="confirm">
                            {{ __('Disable two factor authentication') }}
                        </button>
                    </DisableTwoFactor>
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
        </div>
    </popover>
</template>
