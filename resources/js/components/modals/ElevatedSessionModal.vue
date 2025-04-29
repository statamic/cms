<template>
    <modal name="elevated-session" height="auto" :width="500" @closed="modalClosed" v-slot="{ close }" click-to-close>
        <div class="-max-h-screen-px">
            <div
                class="flex items-center justify-between rounded-t-lg border-b bg-gray-200 px-5 py-3 text-lg font-semibold dark:border-dark-900 dark:bg-dark-550"
            >
                {{ method === 'password_confirmation' ? __('Confirm Your Password') : __('Verification Code') }}
            </div>

            <div class="publish-fields p-2">
                <div class="form-group w-full">
                    <template v-if="method === 'password_confirmation'">
                        <label v-text="__('messages.elevated_session_enter_password')" />
                        <small class="help-block text-red-500" v-if="errors.password" v-text="errors.password[0]" />
                        <div class="flex items-center">
                            <input
                                type="password"
                                v-model="password"
                                ref="password"
                                class="input-text"
                                tabindex="1"
                                autofocus
                                @keydown.enter.prevent="submit"
                            />
                            <button
                                @click="submit(close)"
                                class="btn-primary ltr:ml-2 rtl:mr-2"
                                v-text="__('Confirm')"
                            />
                        </div>
                    </template>

                    <template v-if="method === 'verification_code'">
                        <label v-text="__('messages.elevated_session_enter_verification_code')" />
                        <small
                            class="help-block text-red-500"
                            v-if="errors.verification_code"
                            v-text="errors.verification_code[0]"
                        />
                        <div class="flex items-center">
                            <input
                                type="text"
                                v-model="verificationCode"
                                ref="verificationCode"
                                class="input-text"
                                tabindex="1"
                                autofocus
                                @keydown.enter.prevent="submit"
                            />
                            <button
                                @click="submit(close)"
                                class="btn-primary ltr:ml-2 rtl:mr-2"
                                v-text="__('Confirm')"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        method: String,
    },

    data() {
        return {
            password: null,
            verificationCode: null,
            errors: [],
            shouldResolve: false,
        };
    },

    methods: {
        submit(close) {
            let payload = {
                password: this.password,
                verification_code: this.verificationCode,
            };

            this.$axios
                .post(cp_url('elevated-session'), payload)
                .then((response) => {
                    this.shouldResolve = true;
                    close();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    if (error.response.status === 422) {
                        this.$refs.password?.focus();
                        this.$refs.verificationCode?.focus();
                    }
                });
        },

        modalClosed() {
            this.$emit('closed', this.shouldResolve);
        },
    },
};
</script>
