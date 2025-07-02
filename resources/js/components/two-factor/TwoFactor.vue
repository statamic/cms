<script setup>
import { ref } from 'vue';
import DisableTwoFactor from './Disable.vue';
import TwoFactorSetup from './Setup.vue';
import TwoFactorRecoveryCodesModal from './RecoveryCodesModal.vue';
import { requireElevatedSession } from '@statamic/components/elevated-sessions';
import { Button } from '@statamic/ui';

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
            <Button v-text="__('Two Factor Authentication')" />
        </template>
        <div class="max-w-sm p-4">
            <template v-if="!isSetup">
                <div>
                    <p class="text-gray mb-4 text-sm">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

                    <div class="flex space-x-2">
                        <Button @click="openSetupModal">
                            {{ __('Enable two factor authentication') }}
                        </Button>
                    </div>
                </div>
            </template>

            <template v-else>
                <p class="text-gray mb-4 text-sm">{{ __('statamic::messages.two_factor_enabled') }}</p>

                <div class="flex items-center space-x-4">
                    <Button @click="openRecoveryCodesModal">
                        {{ __('Show recovery codes') }}
                    </Button>

                    <DisableTwoFactor
                        :url="routes.disable"
                        :is-enforced="isEnforced"
                        @reset-complete="resetComplete"
                        v-slot="{ confirm }"
                    >
                        <Button variant="danger" @click="confirm">
                            {{ __('Disable two factor authentication') }}
                        </Button>
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
