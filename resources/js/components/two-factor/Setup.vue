<template>
    <modal v-if="setupModalOpen" name="two-factor-setup" @closed="$emit('cancel')">
        <div>
            <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                <loading-graphic />
            </div>

            <template v-else>
                <div class="-max-h-screen-px">
                    <div
                        class="flex items-center justify-between rounded-t-lg border-b bg-gray-200 px-5 py-3 text-lg font-semibold dark:border-dark-900 dark:bg-dark-550"
                    >
                        {{ __('Set up Two Factor Authentication') }}
                    </div>
                </div>
                <div class="p-5">
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
                            <input
                                type="text"
                                class="input-text"
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
                <div
                    class="flex items-center justify-end border-t bg-gray-200 p-4 text-sm dark:border-dark-900 dark:bg-dark-550"
                >
                    <button
                        class="text-gray hover:text-gray-900 dark:text-dark-150 dark:hover:text-dark-100"
                        @click="$emit('close')"
                        v-text="__('Cancel')"
                    />
                    <button
                        class="btn-primary ltr:ml-4 rtl:mr-4"
                        :disabled="!code"
                        @click="confirm"
                        v-text="__('Confirm')"
                    />
                </div>
            </template>
        </div>
    </modal>

    <TwoFactorRecoveryCodesModal
        v-if="recoveryCodesModalOpen"
        :recovery-codes-url="recoveryCodeUrls.show"
        :generate-url="recoveryCodeUrls.generate"
        :download-url="recoveryCodeUrls.download"
        @close="complete"
    />
</template>

<script>
import LoadingGraphic from '@statamic/components/LoadingGraphic.vue';
import TwoFactorRecoveryCodesModal from '@statamic/components/two-factor/RecoveryCodesModal.vue';

export default {
    components: { TwoFactorRecoveryCodesModal, LoadingGraphic },

    props: {
        enableUrl: String,
        recoveryCodeUrls: Object,
    },

    data() {
        return {
            loading: true,
            qrCode: null,
            secretKey: null,
            code: null,
            error: null,
            confirmUrl: null,
            setupModalOpen: true,
            recoveryCodesModalOpen: false,
        };
    },

    mounted() {
        this.getSetupCode();
    },

    methods: {
        getSetupCode() {
            this.loading = true;

            this.$axios.get(this.enableUrl).then((response) => {
                this.qrCode = response.data.qr;
                this.secretKey = response.data.secret_key;
                this.confirmUrl = response.data.confirm_url;

                this.loading = false;
            });
        },

        confirm() {
            this.$axios
                .post(this.confirmUrl, { code: this.code })
                .then((response) => {
                    this.setupModalOpen = false;
                    this.recoveryCodesModalOpen = true;
                })
                .catch((error) => {
                    this.error = error.response.data.errors.code[0];
                });
        },

        complete() {
            this.recoveryCodesModalOpen = false;

            this.$emit('setup-complete');
        },
    },
};
</script>
