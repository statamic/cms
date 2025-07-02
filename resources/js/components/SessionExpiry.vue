<template>
    <div class="session-expiry">
        <Modal
            v-if="isWarning && !isShowingLogin"
            :open="isWarning && !isShowingLogin"
            :title="__('Your Session is Expiring')"
            class="max-w-[500px]!"
        >
            <ui-description v-text="warningText" />
            <Button @click="extend" variant="primary" icon="rewind" :text="__('Extend Session')" class="w-full" />
        </Modal>

        <Modal :title="__('Resume Your Session')" :open="isShowingLogin" height="auto" class="max-w-[500px]!">
            <div v-if="isUsingOauth" class="space-y-3">
                <ui-description v-text="__('messages.session_expiry_new_window')" />
                <ui-button
                    variant="primary"
                    class="w-full"
                    :href="oauthProvider.loginUrl"
                    target="_blank"
                    :text="__('Log in with :provider', { provider: oauthProvider.label })"
                />
            </div>

            <div v-if="!isUsingOauth">
                <ui-field :errors="errors" class="space-y-3">
                    <ui-description v-text="__('messages.session_expiry_enter_password')" />
                    <div class="flex items-center gap-3">
                        <Input
                            type="password"
                            v-model="password"
                            :viewable="true"
                            ref="password"
                            tabindex="1"
                            autofocus
                            @keydown.enter.prevent="submit"
                        />
                        <Button @click="submit" variant="primary" :text="__('Log in')" />
                    </div>
                </ui-field>
            </div>
        </Modal>

        <Modal
            :title="__('Resume Your Session')"
            :open="isShowingTwoFactorChallenge"
            height="auto"
            class="max-w-[500px]!"
        >
            <div>
                <div v-if="twoFactorMode === 'code'" class="space-y-3">
                    <ui-description v-text="__('messages.session_expiry_enter_two_factor_code')" />
                    <ui-description class="text-red-500" v-if="errors.code" v-text="errors.code[0]" />
                    <div class="flex items-center">
                        <Input
                            name="code"
                            v-model="twoFactorCode"
                            ref="twoFactorCode"
                            tabindex="1"
                            pattern="[0-9]*"
                            maxlength="6"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            @keydown.enter.prevent="submitTwoFactorChallenge"
                        />
                    </div>
                </div>

                <div v-if="twoFactorMode === 'recovery_code'" class="form-group w-full">
                    <label v-text="__('messages.session_expiry_enter_two_factor_recovery_code')" />
                    <ui-description class="text-red-500" v-if="errors.recovery_code" v-text="errors.recovery_code[0]" />
                    <div class="flex items-center">
                        <Input
                            name="recovery_code"
                            v-model="twoFactorRecoveryCode"
                            ref="twoFactorRecoveryCode"
                            tabindex="1"
                            maxlength="21"
                            autofocus
                            autocomplete="off"
                            @keydown.enter.prevent="submitTwoFactorChallenge"
                        />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <Button
                        v-if="twoFactorMode === 'code'"
                        variant="ghost"
                        @click="twoFactorMode = 'recovery_code'"
                        :text="__('Use recovery code')"
                    />
                    <Button
                        v-if="twoFactorMode === 'recovery_code'"
                        variant="ghost"
                        @click="twoFactorMode = 'code'"
                        :text="__('Use one-time code')"
                    />
                    <Button variant="primary" @click="submitTwoFactorChallenge" :text="__('Continue')" />
                </div>
            </template>
        </Modal>
    </div>
</template>

<script>
import { Modal, Input, Button } from '@statamic/ui';

var counter;

export default {
    components: {
        Modal,
        Input,
        Button,
    },

    props: {
        warnAt: Number,
        lifetime: Number,
        email: String,
        oauthProvider: String,
        auth: Object,
    },

    data() {
        return {
            isShowingLogin: false,
            isShowingTwoFactorChallenge: false,
            count: this.lifetime, // The timer used in vue
            remaining: this.lifetime, // The actual time remaining as per server responses
            errors: {},
            password: null,
            twoFactorCode: null,
            twoFactorRecoveryCode: null,
            twoFactorMode: 'code',
            pinging: false,
            lastCount: new Date(),
            isPageHidden: false,
        };
    },

    computed: {
        isWarning() {
            return this.count <= this.warnAt;
        },

        warningText() {
            return this.remaining === 0
                ? __('messages.session_expiry_logged_out_for_inactivity')
                : __('messages.session_expiry_logging_out_in_seconds', { seconds: this.remaining });
        },

        isUsingOauth() {
            return this.oauthProvider != null;
        },
    },

    created() {
        this.startCountdown();

        document.addEventListener('visibilitychange', () => (this.isPageHidden = document.hidden), false);
    },

    watch: {
        count(count) {
            this.isShowingLogin = this.auth.enabled && !this.isShowingTwoFactorChallenge && this.remaining <= 0;

            // While we're in the warning period, we'll check every second so that any
            // activity in another tab is picked up and the count will get restarted.
            const withinWarningPeriod = count <= this.warnAt;

            // We keep track of the last time a count was made. It will be every second while
            // Javascript is being executed, but the count will have stopped if the computer
            // has been put to sleep. If it's been a while since the last count, we'll
            // also perform a timeout check. This will let things recalibrate.
            const secondsSinceLastCount = Math.floor((Date.now() - this.lastCount) / 1000);
            const itsBeenAWhile = secondsSinceLastCount > 10;

            if (withinWarningPeriod || itsBeenAWhile) {
                this.ping().catch((e) => {});
            }

            this.lastCount = new Date();
        },

        isShowingLogin(showing, wasShowing) {
            if (showing && !wasShowing) this.updateCsrfToken();
        },
    },

    methods: {
        startCountdown() {
            counter = setInterval(() => {
                this.count--;
            }, 1000);
        },

        restartCountdown() {
            this.count = this.remaining = this.lifetime;
            this.startCountdown();
        },

        ping() {
            if (this.pinging || this.isPageHidden) return Promise.resolve();

            this.pinging = true;

            return this.$axios
                .get(cp_url('session-timeout'))
                .then((response) => {
                    this.count = this.remaining = response.data;
                })
                .catch((e) => {
                    if (e.response.status === 401) {
                        this.remaining = 0;
                        if (!this.auth.enabled) window.location = this.auth.redirect_to || '/';
                    }
                    throw e;
                })
                .finally((response) => {
                    this.pinging = false;
                });
        },

        updateCsrfToken() {
            return this.$axios.get(cp_url('auth/token')).then((response) => {
                const csrf = response.data;
                this.$axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
                this.$config.set('csrfToken', csrf);
            });
        },

        submit() {
            this.updateCsrfToken().then(() => {
                this.login();
            });
        },

        login() {
            this.$axios
                .post(cp_url('auth/login'), {
                    email: this.email,
                    password: this.password,
                })
                .then((response) => {
                    this.errors = {};
                    this.password = null;
                    this.isShowingLogin = false;

                    if (response.data.two_factor) {
                        this.isShowingTwoFactorChallenge = true;
                        return;
                    }

                    this.loginComplete();
                })
                .catch((e) => {
                    if (e.response.status === 422) {
                        this.errors = e.response.data.errors;
                        this.$toast.error(e.response.data.message);
                    } else {
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },

        submitTwoFactorChallenge() {
            this.$axios
                .post(cp_url('auth/two-factor-challenge'), {
                    code: this.twoFactorCode,
                    recovery_code: this.twoFactorRecoveryCode,
                })
                .then((response) => {
                    this.errors = {};
                    this.twoFactorCode = null;
                    this.twoFactorRecoveryCode = null;
                    this.twoFactorMode = 'code';
                    this.isShowingTwoFactorChallenge = false;
                    this.loginComplete();
                })
                .catch((e) => {
                    if (e.response.status === 422) {
                        this.errors = e.response.data.errors;
                        this.$toast.error(e.response.data.message);
                    } else {
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },

        extend() {
            this.$axios.get(cp_url('auth/extend')).then((response) => {
                this.remaining = this.lifetime;
            });
        },

        loginComplete() {
            this.$toast.success(__('Logged in'));
            this.restartCountdown();
            this.updateCsrfToken();
        },
    },
};
</script>
