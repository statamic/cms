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

        <template v-else-if="isCurrentUser && !isSetup">
            <div class="text-sm">
                <p class="mb-2 font-medium">{{ __('statamic::messages.two_factor_not_setup_1') }}</p>
                <p>{{ __('statamic::messages.two_factor_not_setup_2') }}</p>
            </div>
        </template>

        <template v-else>
            <two-factor-locked v-if="isLocked" :route="meta.routes.unlock" @update="updateState" />

            <div v-if="isCurrentUser">
                <button class="btn" @click="recoveryCodesModalOpen = true">Show recovery codes</button>
            </div>

            <two-factor-reset
                :route="meta.routes.reset"
                :enforced="isCurrentUser"
                :language-user="languageUser"
                @update="updateState"
            />
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
import TwoFactorReset from './two-factor/Reset.vue';
import TwoFactorSetup from './two-factor/Setup.vue';
import TwoFactorRecoveryCodesModal from './two-factor/RecoveryCodesModal.vue';

export default {
    mixins: [Fieldtype],

    components: {
        TwoFactorLocked,
        TwoFactorReset,
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

        languageUser() {
            return (this.meta.is_current_user ? 'me' : 'user') + (this.meta.is_enforced ? '_enforced' : '');
        },
    },

    methods: {
        updateState(field, status) {
            // update the status
            this.$data[field] = status;
        },

        setupComplete() {
            this.isSetup = true;
            this.recoveryCodesModalOpen = true;
        },
    },
};
</script>
