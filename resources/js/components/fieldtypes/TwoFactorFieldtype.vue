<template>
    <div>
            <two-factor-enable :route="meta.routes.setup" />
        <template v-if="isCurrentUser && !isSetup">
        </template>

        <template v-else-if="isCurrentUser && !isSetup">
            <div class="text-sm">
                <p class="mb-2 font-medium">{{ __('statamic::messages.two_factor_not_setup_1') }}</p>
                <p>{{ __('statamic::messages.two_factor_not_setup_2') }}</p>
            </div>
        </template>

        <template v-else>
            <two-factor-locked v-if="isLocked" :route="meta.routes.unlock" @update="updateState" />

            <two-factor-recovery-codes v-if="isCurrentUser" :routes="meta.routes.recovery_codes" />

            <two-factor-reset
                :route="meta.routes.reset"
                :enforced="isCurrentUser"
                :language-user="languageUser"
                @update="updateState"
            />
        </template>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import TwoFactorEnable from './two-factor/Enable.vue';
import TwoFactorLocked from './two-factor/Locked.vue';
import TwoFactorRecoveryCodes from './two-factor/RecoveryCodes.vue';
import TwoFactorReset from './two-factor/Reset.vue';

export default {
    mixins: [Fieldtype],

    components: {
        TwoFactorEnable,
        TwoFactorLocked,
        TwoFactorRecoveryCodes,
        TwoFactorReset,
    },

    data() {
        return {
            isLocked: this.meta.is_locked,
            isSetup: this.meta.is_setup,
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
    },
};
</script>
