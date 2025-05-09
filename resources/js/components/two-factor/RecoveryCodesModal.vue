<script setup>
import { ref, onMounted } from 'vue';
import LoadingGraphic from '@statamic/components/LoadingGraphic.vue';
import axios from 'axios';

const emit = defineEmits(['cancel', 'close']);

const props = defineProps({
    recoveryCodesUrl: String,
    generateUrl: String,
    downloadUrl: String,
});

const loading = ref(true);
const confirming = ref(false);
const recoveryCodes = ref(null);
const canCopy = !!navigator.clipboard;

onMounted(() => getRecoveryCodes());

function getRecoveryCodes() {
    loading.value = true;

    axios.get(props.recoveryCodesUrl).then((response) => {
        recoveryCodes.value = response.data.recovery_codes;
        loading.value = false;
    });
}

function regenerate() {
    axios.post(props.generateUrl).then((response) => {
        recoveryCodes.value = response.data.recovery_codes;
        confirming.value = false;
        Statamic.$toast.success(__('Refreshed recovery codes'));
    });
}

function copyToClipboard() {
    if (!canCopy) return Statamic.$toast.error(__('Unable to copy to clipboard'));

    navigator.clipboard
        .writeText(recoveryCodes.value.join('\n'))
        .then(() => Statamic.$toast.success(__('Copied to clipboard')))
        .catch((error) => Statamic.$toast.error(__('Unable to copy to clipboard')));
}
</script>

<template>
    <modal name="two-factor-recovery-codes" @closed="$emit('cancel')">
        <div>
            <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                <loading-graphic />
            </div>

            <template v-else>
                <div class="-max-h-screen-px">
                    <div
                        class="flex items-center justify-between rounded-t-lg border-b bg-gray-200 px-5 py-3 text-lg font-semibold dark:border-dark-900 dark:bg-dark-550"
                    >
                        {{ __('Recovery Codes') }}
                    </div>
                </div>
                <div class="p-5">
                    <p class="mb-6">{{ __('statamic::messages.two_factor_recovery_codes') }}</p>

                    <div class="mb-3 bg-gray-200 p-4">
                        <ul class="grid gap-2 md:grid-cols-2">
                            <li
                                v-for="recoveryCode in recoveryCodes"
                                class="font-mono text-sm"
                                v-text="recoveryCode"
                            ></li>
                        </ul>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button class="btn" v-if="canCopy" @click="copyToClipboard">{{ __('Copy') }}</button>

                        <a class="btn" :href="downloadUrl" download>{{ __('Download') }}</a>

                        <button class="btn" @click.prevent="confirming = true">
                            {{ __('Refresh recovery codes') }}
                        </button>
                    </div>
                </div>
                <div
                    class="flex items-center justify-end border-t bg-gray-200 p-4 text-sm dark:border-dark-900 dark:bg-dark-550"
                >
                    <button class="btn" @click="$emit('close')" v-text="__('Close')" />
                </div>
            </template>
        </div>
    </modal>

    <confirmation-modal
        v-if="confirming"
        :danger="true"
        :title="__('Are you sure?')"
        @cancel="confirming = false"
        @confirm="regenerate"
    >
        <p>{{ __('statamic::messages.two_factor_regenerate_recovery_codes') }}</p>
    </confirmation-modal>
</template>
