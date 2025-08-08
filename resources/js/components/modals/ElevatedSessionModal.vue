<template>
    <Modal :title="title" :open="open" @update:open="modalClosed">
        <div class="publish-fields p-2">
            <div class="form-group w-full">
                <template v-if="method === 'password_confirmation'">
                    <Field
                        :instructions="__('messages.elevated_session_enter_password')"
                        :errors="errors.password"
                        required
                    >
                        <div class="flex items-center">
                            <Input
                                type="password"
                                v-model="password"
                                ref="password"
                                tabindex="1"
                                autofocus
                                @keydown.enter.prevent="submit"
                            />
                            <Button
                                @click="submit"
                                class="ms-2"
                                :text="__('Confirm')"
                                variant="primary"
                            />
                        </div>
                    </Field>
                </template>

                <template v-if="method === 'verification_code'">
                    <Field
                        :instructions="__('messages.elevated_session_enter_verification_code')"
                        :errors="errors.verification_code"
                        required
                    >
                        <div class="flex items-center">
                            <Input
                                type="text"
                                v-model="verificationCode"
                                ref="verificationCode"
                                tabindex="1"
                                autofocus
                                @keydown.enter.prevent="submit"
                            />
                            <Button
                                @click="resendCode"
                                class="ms-2"
                                :disabled="resendDisabled"
                                :text="__('Resend code')"
                            />
                            <Button
                                @click="submit"
                                class="ms-2"
                                variant="primary"
                                :text="__('Confirm')"
                            />
                        </div>
                    </Field>
                </template>
            </div>
        </div>
    </Modal>
</template>

<script>
import { Modal, Field, Input, Button } from '@statamic/ui';

export default {
    components: {
        Modal,
        Field,
        Input,
        Button,
    },

    props: {
        method: String,
    },

    data() {
        return {
            password: null,
            verificationCode: null,
            errors: [],
            shouldResolve: false,
            resendDisabled: false,
            open: true,
        };
    },

    computed: {
        title() {
            return this.method === 'password_confirmation' ? __('Confirm Your Password') : __('Verification Code');
        },
    },

    methods: {
        submit() {
            let payload = {
                password: this.password,
                verification_code: this.verificationCode,
            };

            this.$axios
                .post(cp_url('elevated-session'), payload)
                .then((response) => {
                    this.shouldResolve = true;
                    this.modalClosed();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    if (error.response.status === 422) {
                        this.$refs.password?.focus();
                        this.$refs.verificationCode?.focus();
                    }
                });
        },

        resendCode() {
            this.$axios
                .get(cp_url('elevated-session/resend-code'))
                .then(() => this.$toast.success(__('messages.elevated_session_verification_code_sent')))
                .catch((error) =>
                    this.$toast.error(
                        error.response.status === 429 ? error.response.data.error : __('Something went wrong'),
                    ),
                );

            this.resendDisabled = true;
            setTimeout(() => (this.resendDisabled = false), 60 * 1000);
        },

        modalClosed() {
            this.open = false;
            this.$emit('closed', this.shouldResolve);
        },
    },
};
</script>
