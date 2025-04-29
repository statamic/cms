<template>
    <popover placement="bottom" ref="popper">
        <template #trigger>
            <button class="btn" v-text="__('Two Factor Authentication')" />
        </template>
        <div class="max-w-sm p-4">
            <template v-if="isCurrentUser && !isSetup">
                <div>
                    <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

                    <div class="flex space-x-2">
                        <button class="btn" @click="openSetupModal">
                            {{ __('Enable two factor authentication') }}
                        </button>
                    </div>
                </div>
            </template>

            <template v-else-if="!isCurrentUser && !isSetup">
                <p class="text-sm text-gray">{{ __('statamic::messages.two_factor_not_setup') }}</p>
            </template>

            <template v-else-if="isCurrentUser || canDisable">
                <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enabled') }}</p>

                <div class="flex items-center space-x-4">
                    <button v-if="isCurrentUser" class="btn" @click="openRecoveryCodesModal">
                        {{ __('Show recovery codes') }}
                    </button>

                    <DisableTwoFactor
                        v-if="canDisable"
                        :url="routes.disable"
                        :is-current-user="isCurrentUser"
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

            <template v-else>
                <p class="mb-4 text-sm text-gray">
                    {{ __('statamic::messages.two_factor_cant_manage_without_permission') }}
                </p>
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

<script>
import DisableTwoFactor from './Disable.vue';
import TwoFactorSetup from './Setup.vue';
import TwoFactorRecoveryCodesModal from './RecoveryCodesModal.vue';
import { requireElevatedSession } from '@statamic/components/elevated-sessions';

export default {
    components: {
        DisableTwoFactor,
        TwoFactorSetup,
        TwoFactorRecoveryCodesModal,
    },

    props: ['isSetup', 'isCurrentUser', 'isEnforced', 'routes', 'canDisable'],

    data() {
        return {
            recoveryCodesModalOpen: false,
            setupModalOpen: false,
        };
    },

    methods: {
        openSetupModal() {
            requireElevatedSession()
                .then(() => (this.setupModalOpen = true))
                .catch(() => this.$toast.error(__('statamic::messages.elevated_session_required')));
        },

        openRecoveryCodesModal() {
            requireElevatedSession()
                .then(() => (this.recoveryCodesModalOpen = true))
                .catch(() => this.$toast.error(__('statamic::messages.elevated_session_required')));
        },

        setupComplete() {
            this.isSetup = true;
            this.setupModalOpen = false;
        },

        resetComplete() {
            this.isSetup = false;
        },
    },
};
</script>
