<template>
    <div>
        <template v-if="!meta.enabled">
            <div class="text-sm">
                <p>{{ __('statamic::messages.two_factor_not_enabled') }}</p>
            </div>
        </template>

        <template v-else-if="meta.is_me && !setup">
            <two-factor-enable :route="meta.routes.setup"/>
        </template>

        <template v-else-if="!meta.is_me && !setup">
            <div class="text-sm">
                <p class="font-medium mb-2">{{ __('statamic::messages.two_factor_not_setup_1') }}</p>
                <p>{{ __('statamic::messages.two_factor_not_setup_2') }}</p>
            </div>
        </template>

        <template v-else>
            <two-factor-locked
                v-if="locked"
                :route="meta.routes.locked"
                @update="updateState"/>

            <two-factor-recovery-codes
                v-if="meta.is_me"
                :routes="meta.routes.recovery_codes"/>

            <two-factor-reset
                :route="meta.routes.reset"
                :enforced="meta.is_enforced"
                :language-user="languageUser"
                @update="updateState"/>
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
        TwoFactorReset
    },

    data() {
        return {
            locked: false,
            setup: false
        }
    },

    mounted() {
        this.locked = this.meta.is_locked;
        this.setup = this.meta.is_setup;
    },

    computed: {
        languageUser() {
            return (this.meta.is_me ? 'me' : 'user') + (this.meta.is_enforced ? '_enforced' : '');
        }
    },

    methods: {
        updateState(field, status) {
            // update the status
            this.$data[field] = status;
        }
    },
};
</script>
