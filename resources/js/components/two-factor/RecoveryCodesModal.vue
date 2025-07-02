<script setup>
import { ref, onMounted } from 'vue';
import LoadingGraphic from '@statamic/components/LoadingGraphic.vue';
import axios from 'axios';
import { Modal, Button } from '@statamic/ui';

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
    <Modal :title="__('Recovery Codes')" :open="true" @update:open="$emit('cancel')">
        <div>
            <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                <loading-graphic />
            </div>

            <template v-else>
                <div class="space-y-6">
                    <ui-description>{{ __('statamic::messages.two_factor_recovery_codes') }}</ui-description>

                    <div class="rounded-xl bg-gray-200 py-8">
                        <ul class="grid justify-center gap-2 text-center md:grid-cols-2">
                            <li
                                v-for="recoveryCode in recoveryCodes"
                                class="font-mono lg:text-base"
                                v-text="recoveryCode"
                            ></li>
                        </ul>
                    </div>

                    <div class="flex items-center space-x-4">
                        <Button v-if="canCopy" @click="copyToClipboard">{{ __('Copy') }}</Button>

                        <Button :href="downloadUrl" download>{{ __('Download') }}</Button>

                        <Button @click.prevent="confirming = true">
                            {{ __('Refresh recovery codes') }}
                        </Button>
                    </div>
                </div>
            </template>
        </div>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button variant="primary" @click="$emit('close')" :text="__('Close')" />
            </div>
        </template>
    </Modal>

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
