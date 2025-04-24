<template>
    <div>
        <template v-if="isCurrentUser && !isSetup">
            <div>
                <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

                <div class="flex space-x-2">
                    <button class="btn" @click="setupModalOpen = true">
                        {{ __('Enable two factor authentication') }}
                    </button>
                </div>
            </div>
        </template>

        <template v-else-if="!isCurrentUser && !isSetup">
            <p class="text-sm text-gray">{{ __('statamic::messages.two_factor_not_setup') }}</p>
        </template>

        <template v-else-if="isCurrentUser || meta.can_disable">
            <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enabled') }}</p>

            <div class="flex items-center space-x-4">
                <button v-if="isCurrentUser" class="btn" @click="recoveryCodesModalOpen = true">
                    {{ __('Show recovery codes') }}
                </button>

                <DisableTwoFactor
                    v-if="meta.can_disable"
                    :url="meta.routes.disable"
                    :is-current-user="isCurrentUser"
                    :is-enforced="isEnforced"
                    @reset-complete="resetComplete"
                    v-slot="{ confirm }"
                >
                    <button class="btn-danger" @click="confirm">{{ __('Disable two factor authentication') }}</button>
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
            :enable-url="meta.routes.enable"
            :recovery-code-urls="meta.routes.recovery_codes"
            @close="setupModalOpen = false"
            @setup-complete="setupComplete"
        />

        <TwoFactorRecoveryCodesModal
            v-if="recoveryCodesModalOpen"
            :recovery-codes-url="meta.routes.recovery_codes.show"
            :generate-url="meta.routes.recovery_codes.generate"
            :download-url="meta.routes.recovery_codes.download"
            @close="recoveryCodesModalOpen = false"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import DisableTwoFactor from './two-factor/Disable.vue';
import TwoFactorSetup from './two-factor/Setup.vue';
import TwoFactorRecoveryCodesModal from './two-factor/RecoveryCodesModal.vue';

export default {
    mixins: [Fieldtype],

    components: {
        DisableTwoFactor,
        TwoFactorSetup,
        TwoFactorRecoveryCodesModal,
    },

    data() {
        return {
            isLocked: this.meta.is_locked,
            isSetup: this.meta.is_setup,
            recoveryCodesModalOpen: false,
            setupModalOpen: false,
        };
    },

    computed: {
        isCurrentUser() {
            return this.meta.is_current_user;
        },

        isEnforced() {
            return this.meta.is_enforced;
        },
    },

    methods: {
        setupComplete() {
            this.isSetup = true;
            this.setupModalOpen = false;
        },

        resetComplete() {
            this.isSetup = false;
            this.isLocked = false;
        },
    },
};
</script>
