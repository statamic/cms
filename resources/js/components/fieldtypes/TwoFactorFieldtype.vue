<template>
    <div>
        <template v-if="isCurrentUser && !isSetup">
            <div>
                <p class="mb-4 text-sm text-gray">{{ __('statamic::messages.two_factor_enable_introduction') }}</p>

                <div class="flex space-x-2">
                    <button class="btn" @click="setupModalOpen = true">{{ __('Enable two factor authentication') }}</button>
                </div>

                <TwoFactorSetup
                    v-if="setupModalOpen"
                    :setup-url="meta.routes.setup"
                    @setup-complete="setupComplete"
                    @cancel="setupModalOpen = false"
                />
            </div>
        </template>

        <template v-else-if="!isCurrentUser && !isSetup">
            <p class="text-sm text-gray">{{ __('statamic::messages.two_factor_not_setup') }}</p>
        </template>

        <template v-else>
<!--            <two-factor-locked v-if="isLocked" :route="meta.routes.unlock" @update="updateState" />-->

            <div class="flex items-center space-x-4">
                <button v-if="isCurrentUser" class="btn" @click="recoveryCodesModalOpen = true">Show recovery codes</button>

                <DisableTwoFactor
                    :url="meta.routes.disable"
                    :is-current-user="isCurrentUser"
                    :is-enforced="isEnforced"
                    @reset-complete="resetComplete"
                    v-slot="{ confirm }"
                >
                    <button class="btn-danger" @click="confirm">Disable two factor authentication</button>
                </DisableTwoFactor>
            </div>
        </template>

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
import TwoFactorLocked from './two-factor/Locked.vue';
import DisableTwoFactor from './two-factor/Disable.vue';
import TwoFactorSetup from './two-factor/Setup.vue';
import TwoFactorRecoveryCodesModal from './two-factor/RecoveryCodesModal.vue';

export default {
    mixins: [Fieldtype],

    components: {
        TwoFactorLocked,
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
            this.recoveryCodesModalOpen = true;
        },

        resetComplete() {
            this.isSetup = false;
            this.isLocked = false;
        },
    },
};
</script>
