<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Modal, Button, Icon } from '@/components/ui';
import useCopy from '@/composables/copy';

const emit = defineEmits(['cancel', 'close']);

const props = defineProps({
    recoveryCodesUrl: String,
    generateUrl: String,
    downloadUrl: String,
});

const loading = ref(true);
const confirming = ref(false);
const recoveryCodes = ref(null);
const { copySupported, copy } = useCopy();

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
</script>

<template>
    <Modal :title="__('Recovery Codes')" open @update:open="$emit('cancel')">
        <div>
            <div v-if="loading" class="flex items-center justify-center text-center">
                <Icon name="loading" />
            </div>

            <template v-else>
                <div class="space-y-6">
                    <ui-description>{{ __('statamic::messages.two_factor_recovery_codes') }}</ui-description>

                    <div class="bg-gray-200 dark:bg-gray-800 py-8 rounded-xl">
                        <ul class="grid gap-2 md:grid-cols-2 text-center justify-center">
                            <li
                                v-for="recoveryCode in recoveryCodes"
                                class="font-mono lg:text-base"
                                v-text="recoveryCode"
                            ></li>
                        </ul>
                    </div>

                    <div class="flex items-center space-x-4">
                        <Button v-if="copySupported" @click="copy(recoveryCodes.join('\n'))">{{ __('Copy') }}</Button>

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
        :open="confirming"
        :danger="true"
        :title="__('Are you sure?')"
        @cancel="confirming = false"
        @confirm="regenerate"
    >
        <p>{{ __('statamic::messages.two_factor_regenerate_recovery_codes') }}</p>
    </confirmation-modal>
</template>
