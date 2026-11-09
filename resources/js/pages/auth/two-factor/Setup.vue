<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import TwoFactorSetup from '@/components/two-factor/Setup.vue';
import { requireElevatedSession } from '@/components/elevated-sessions';
import { AuthCard, Button } from '@ui';
import { ref } from 'vue';

defineOptions({ layout: Outside });

const props = defineProps(['routes', 'redirect']);

const setupModalOpen = ref(false);

function openSetupModal() {
    requireElevatedSession()
        .then(() => (setupModalOpen.value = true))
        .catch(() => Statamic.$toast.error(__('statamic::messages.elevated_session_required')));
}

function setupComplete() {
    window.location.href = props.redirect;
}
</script>

<template>
    <Head :title="__('Two-Factor Authentication')" />

    <AuthCard
        icon="phone-lock"
        :title="__('Set up Two Factor Authentication')"
        :description="__('statamic::messages.two_factor_account_requirement')"
    >
        <Button variant="primary" @click="openSetupModal" :text="__('Set up')" class="w-full" />

        <TwoFactorSetup
            v-if="setupModalOpen"
            :enable-url="routes.enable"
            :recovery-code-urls="routes.recovery_codes"
            @close="setupModalOpen = false"
            @setup-complete="setupComplete"
        />
    </AuthCard>
</template>
