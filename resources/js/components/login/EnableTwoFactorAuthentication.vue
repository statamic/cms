<template>
    <button type="button" class="btn-primary" @click="setupModalOpen = true">{{ __('Set up') }}</button>

    <TwoFactorSetup
        v-if="setupModalOpen"
        :enable-url="routes.enable"
        :recovery-code-urls="routes.recovery_codes"
        @close="setupModalOpen = false"
        @setup-complete="setupComplete"
    />
</template>

<script>
import TwoFactorSetup from '@statamic/components/two-factor/Setup.vue';

export default {
    components: { TwoFactorSetup },

    props: {
        routes: Object,
        redirect: String,
    },

    data() {
        return {
            busy: false,
            setupModalOpen: false,
        };
    },

    methods: {
        setupComplete() {
            window.location.href = this.redirect;
        },
    },
};
</script>
