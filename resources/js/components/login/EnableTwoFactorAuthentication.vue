<script setup>
import TwoFactorSetup from '@/components/two-factor/Setup.vue';
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
    <ui-button variant="primary" @click="setupModalOpen = true" :text="__('Set up')" class="w-full" />

    <TwoFactorSetup
        v-if="setupModalOpen"
        :enable-url="routes.enable"
        :recovery-code-urls="routes.recovery_codes"
        @close="setupModalOpen = false"
        @setup-complete="setupComplete"
    />
</template>
