<script setup>
import { ref, onMounted } from 'vue';
import LoadingGraphic from '@statamic/components/LoadingGraphic.vue';
import TwoFactorRecoveryCodesModal from '@statamic/components/two-factor/RecoveryCodesModal.vue';
import axios from 'axios';
import { Modal, Input, Button } from '@statamic/ui';

const emit = defineEmits(['setup-complete', 'cancel', 'cancel']);

const props = defineProps({
    enableUrl: String,
    recoveryCodeUrls: Object,
});

const loading = ref(true);
const qrCode = ref(null);
const secretKey = ref(null);
const code = ref(null);
const error = ref(null);
const confirmUrl = ref(null);
const setupModalOpen = ref(true);
const recoveryCodesModalOpen = ref(false);

onMounted(() => getSetupCode());

function getSetupCode() {
    loading.value = true;

    axios.get(props.enableUrl).then((response) => {
        qrCode.value = response.data.qr;
        secretKey.value = response.data.secret_key;
        confirmUrl.value = response.data.confirm_url;
        loading.value = false;
    });
}

function confirm() {
    axios
        .post(confirmUrl.value, { code: code.value })
        .then((response) => {
            setupModalOpen.value = false;
            recoveryCodesModalOpen.value = true;
        })
        .catch((error) => {
            error.value = error.response.data.errors.code[0];
        });
}

function complete() {
    recoveryCodesModalOpen.value = false;
    emit('setup-complete');
}
</script>

<template>
    <Modal v-if="setupModalOpen" :title="__('Set up Two Factor Authentication')" :open="true" @update:model-value="$emit('cancel')">
        <div>
            <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                <loading-graphic />
            </div>

            <template v-else>
                <div>
                    <p class="mb-6">{{ __('statamic::messages.two_factor_setup_instructions') }}</p>

                    <div class="flex justify-center space-x-6">
                        <div class="bg-white" v-html="qrCode"></div>
                        <div>
                            <p>
                                {{ __('Setup Key') }}: <code>{{ secretKey }}</code>
                            </p>

                            <label
                                for="code"
                                class="mb-2 mt-4 block text-sm font-medium text-gray-700 dark:text-dark-150"
                            >
                                {{ __('Verification Code') }}
                            </label>
                            <Input
                                name="code"
                                pattern="[0-9]*"
                                maxlength="6"
                                inputmode="numeric"
                                autofocus
                                autocomplete="off"
                                v-model="code"
                            />
                            <div v-if="error" class="mt-2 text-xs text-red-500" v-html="error"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button
                    variant="ghost"
                    @click="$emit('close')"
                    :text="__('Cancel')"
                />
                <Button
                    :disabled="!code"
                    variant="primary"
                    @click="confirm"
                    :text="__('Confirm')"
                />
            </div>
        </template>
    </Modal>

    <TwoFactorRecoveryCodesModal
        v-if="recoveryCodesModalOpen"
        :recovery-codes-url="recoveryCodeUrls.show"
        :generate-url="recoveryCodeUrls.generate"
        :download-url="recoveryCodeUrls.download"
        @close="complete"
    />
</template>
