<script setup>
import TwoFactorSetup from '@statamic/components/two-factor/Setup.vue';
import { ref } from 'vue';

const props = defineProps({
    routes: Object,
    redirect: String,
});

const setupModalOpen = ref(false);

function setupComplete() {
    window.location.href = props.redirect;
}
</script>

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
