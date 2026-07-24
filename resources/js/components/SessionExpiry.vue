<template>
    <div class="session-expiry">
        <Modal
            v-if="isShowingWarningModal"
            :open="isShowingWarningModal"
            :title="__('Your Session is Expiring')"
            class="max-w-[500px]!"
            :dismissible="false"
        >
            <ui-description v-text="warningText" />

            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <Button @click="dismissWarning" variant="ghost" :text="__('Cancel')" />
                    <Button @click="extend" variant="primary" icon="rewind" :text="__('Extend Session')" />
                </div>
            </template>
        </Modal>

        <button
            v-if="banner"
            type="button"
            @click="banner.resume"
            class="fixed top-0 inset-x-0 z-(--z-index-portal) flex items-center justify-center gap-2 bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-md hover:bg-red-700"
        >
            <ui-icon name="alert-alarm-bell" class="size-4" />
            <span v-text="banner.text" />
        </button>

        <Modal :title="__('Resume Your Session')" :open="isShowingLoginModal" height="auto" class="max-w-[500px]!" :dismissible="false">
            <div v-if="isUsingOauth" class="space-y-3">
                <ui-description v-text="__('messages.session_expiry_new_window')" />
                <ui-button variant="primary" class="w-full" :href="oauthProvider.loginUrl" target="_blank" :text="__('Log in with :provider', { provider: oauthProvider.label })" />
            </div>

            <div v-if="!isUsingOauth">
                <ui-field :errors="errors" class="space-y-3">
                    <ui-description v-text="__('messages.session_expiry_enter_password')" />
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Input
                            type="password"
                            v-model="password"
                            :viewable="true"
                            ref="password"
                            :tabindex="1"
                            autofocus
                            @keydown.enter.prevent="submit"
                        />
                        <Button @click="submit" variant="primary" :text="__('Log in')" />
                    </div>
                </ui-field>
            </div>

            <template #footer>
                <div class="flex items-center justify-end pt-3 pb-1">
                    <Button @click="dismissLogin" variant="ghost" :text="__('Cancel')" />
                </div>
            </template>
        </Modal>

        <Modal :title="__('Resume Your Session')" :open="isShowingTwoFactorChallenge" height="auto" class="max-w-[500px]!" :dismissible="false">
            <div>
                <div v-if="twoFactorMode === 'code'" class="space-y-3">
                    <ui-description v-text="__('messages.session_expiry_enter_two_factor_code')" />
                    <ui-description class="text-red-600" v-if="errors.code" v-text="errors.code[0]" />
                    <div class="flex items-center">
                        <Input
                            name="code"
                            v-model="twoFactorCode"
                            ref="twoFactorCode"
                            :tabindex="1"
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
                    <ui-description
                        class="text-red-600"
                        v-if="errors.recovery_code"
                        v-text="errors.recovery_code[0]"
                    />
                    <div class="flex items-center">
                        <Input
                            name="recovery_code"
                            v-model="twoFactorRecoveryCode"
                            ref="twoFactorRecoveryCode"
                            :tabindex="1"
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
                    <Button
                        variant="primary"
                        @click="submitTwoFactorChallenge"
                        :text="__('Continue')"
                    />
                </div>
            </template>
        </Modal>
    </div>
</template>

<script>
import { Modal, Input, Button } from '@/components/ui';
import useStatamicPageProps from '@/composables/page-props.js';

var counter;

export default {
    components: {
        Modal,
        Input,
        Button,
    },

    data() {
        const { sessionExpiry: { warnAt, email, lifetime, oauthProvider, auth }} = useStatamicPageProps();

        return {
            warnAt,
            email,
            lifetime,
            oauthProvider,
            auth,
            isShowingLogin: false,
            isShowingTwoFactorChallenge: false,
            count: lifetime, // The timer used in vue
            remaining: lifetime, // The actual time remaining as per server responses
            errors: {},
            password: null,
            twoFactorCode: null,
            twoFactorRecoveryCode: null,
            twoFactorMode: 'code',
            pinging: false,
            lastCount: new Date(),
            isPageHidden: false,
            dismissedWarning: false,
            dismissedLogin: false,
        };
    },

    computed: {
        isWarning() {
            return this.count <= this.warnAt;
        },

        isShowingWarningModal() {
            return this.isWarning && !this.isShowingLogin && !this.isShowingTwoFactorChallenge && !this.dismissedWarning;
        },

        isShowingLoginModal() {
            return this.isShowingLogin && !this.dismissedLogin;
        },

        // A single banner is shown whenever one of the modals has been explicitly
        // dismissed. Clicking it resumes the flow by reopening that modal.
        banner() {
            if (this.isShowingLogin && this.dismissedLogin) {
                return {
                    text: __('messages.session_expiry_dismissed_login_banner'),
                    resume: this.resumeLogin,
                };
            }

            if (this.isWarning && !this.isShowingLogin && !this.isShowingTwoFactorChallenge && this.dismissedWarning) {
                return {
                    text: __('messages.session_expiry_dismissed_banner'),
                    resume: this.resumeWarning,
                };
            }

            return null;
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

            // Whenever we stop needing to log back in - whether they did so through the
            // reopened modal, or the session was extended elsewhere before they got
            // around to it - reset the dismissed state so that a subsequent expiry
            // shows the modal normally rather than staying stuck on the banner.
            if (!showing) this.dismissedLogin = false;
        },

        // When we leave the warning period (e.g. the session was extended in another
        // tab, or a fresh countdown began), reset the dismissed state so the modal
        // will show normally the next time the warning period is entered.
        isWarning(isWarning) {
            if (!isWarning) this.dismissedWarning = false;
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

        dismissWarning() {
            this.dismissedWarning = true;
        },

        resumeWarning() {
            this.dismissedWarning = false;
        },

        dismissLogin() {
            this.dismissedLogin = true;
        },

        resumeLogin() {
            this.dismissedLogin = false;
        },

        loginComplete() {
            this.$toast.success(__('Logged in'));
            this.restartCountdown();
            this.updateCsrfToken();
            this.restoreSelectedSite();
        },

        restoreSelectedSite() {
            const site = Statamic.$config.get('selectedSite');
            if (site) this.$axios.get(cp_url(`select-site/${site}`));
        },
    },
};
</script>
