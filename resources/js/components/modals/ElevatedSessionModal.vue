<template>
    <modal name="elevated-session" height="auto" :width="500" @closed="modalClosed" v-slot="{ close }" click-to-close>
        <div class="-max-h-screen-px">
            <div
                class="flex items-center justify-between rounded-t-lg border-b bg-gray-200 px-5 py-3 text-lg font-semibold dark:border-dark-900 dark:bg-dark-550"
            >
                {{ __('Confirm Your Password') }}
            </div>

            <div class="publish-fields p-2">
                <div class="form-group w-full">
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
                        <button @click="submit(close)" class="btn-primary ltr:ml-2 rtl:mr-2" v-text="__('Confirm')" />
                    </div>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    data() {
        return {
            password: null,
            errors: [],
            shouldResolve: false,
        };
    },

    methods: {
        submit(close) {
            this.$axios
                .post(cp_url('elevated-session'), { password: this.password })
                .then((response) => {
                    this.shouldResolve = true;
                    close();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    if (error.response.status === 422) {
                        this.$refs.password.focus();
                    }
                });
        },

        modalClosed() {
            this.$emit('closed', this.shouldResolve);
        },
    },
};
</script>
