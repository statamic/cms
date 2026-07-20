<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Modal, ModalClose, Button, Field, Input, ErrorMessage } from '@ui';

const props = defineProps({
    url: { type: String, required: true },
    hasLicenseKey: Boolean,
});

const emit = defineEmits(['enabled']);

const open = defineModel('open', { type: Boolean, default: false });

const enablingPro = ref(false);
const licenseKey = ref('');
const licenseKeyError = ref(null);

watch(open, (isOpen) => {
    if (!isOpen) {
        licenseKey.value = '';
        licenseKeyError.value = null;
    }
});

const trimmedLicenseKey = computed(() => licenseKey.value.trim());

function validateLicenseKey() {
    if (!trimmedLicenseKey.value && !props.hasLicenseKey) {
        licenseKeyError.value = __('statamic::messages.enable_pro_license_key_required');
        return false;
    }

    licenseKeyError.value = null;
    return true;
}

function enablePro() {
    if (!validateLicenseKey()) {
        return;
    }

    enablingPro.value = true;

    router.post(props.url, {
        license_key: trimmedLicenseKey.value || null,
    }, {
        onSuccess: () => {
            open.value = false;
            emit('enabled');
        },
        onError: (errors) => {
            licenseKeyError.value = Array.isArray(errors.license_key)
                ? errors.license_key[0]
                : errors.license_key;
        },
        onFinish: () => {
            enablingPro.value = false;
        },
    });
}
</script>

<template>
    <Modal
        v-model:open="open"
        :title="__('Enable Statamic Pro')"
        :dismissible="!enablingPro"
        blur
    >
        <div class="space-y-4">
            <p class="text-gray-700 dark:text-gray-200 antialiased">
                {{ __('statamic::messages.enable_pro_license_required') }}
            </p>

            <div v-if="!hasLicenseKey" class="space-y-2" @keydown.enter="enablePro">
                <Field
                    :instructions="__('statamic::messages.enable_pro_license_key_instructions')"
                    instructions-below
                >
                    <Input
                        v-model="licenseKey"
                        name="license_key"
                        autocomplete="off"
                        spellcheck="false"
                        :placeholder="__('Enter License Key')"
                        :disabled="enablingPro"
                    />
                    <ErrorMessage v-if="licenseKeyError" :text="licenseKeyError" class="mt-2" />
                </Field>
            </div>

            <p
                v-else
                class="text-sm text-gray-600 dark:text-gray-400 antialiased"
            >
                {{ __('A license key is already configured for this site.') }}
            </p>
        </div>

        <template #footer>
            <div class="flex items-center justify-end gap-3 pt-3 pb-1">
                <ModalClose asChild>
                    <Button
                        variant="ghost"
                        :disabled="enablingPro"
                        :text="__('Cancel')"
                    />
                </ModalClose>
                <Button
                    variant="primary"
                    :disabled="enablingPro"
                    :loading="enablingPro"
                    :text="__('Enable Pro Mode')"
                    @click="enablePro"
                />
            </div>
        </template>
    </Modal>
</template>
