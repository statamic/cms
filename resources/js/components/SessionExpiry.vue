<template>

    <div class="session-expiry">

        <button v-if="isWarning" class="session-expiry-stripe" @click="extend" v-text="warningText" />

        <modal name="session-timeout-login" v-if="isShowingLogin" height="auto" :width="500">
            <div class="-max-h-screen-px">
            <div class="text-lg font-semibold px-5 py-3 bg-gray-200 dark:bg-dark-550 rounded-t-lg flex items-center justify-between border-b dark:border-dark-900">
                {{ __('Resume Your Session') }}
            </div>

            <div v-if="isUsingOauth" class="p-5">
                <a :href="oauthProvider.loginUrl" target="_blank" class="btn-primary">
                    {{ __('Log in with :provider', {provider: oauthProvider.label}) }}
                </a>
                <div class="text-2xs text-gray mt-4">
                    {{ __('messages.session_expiry_new_window') }}
                </div>
            </div>

            <div v-if="!isUsingOauth" class="publish-fields p-2">
                <div class="form-group w-full">
                    <label v-text="__('messages.session_expiry_enter_password')" />
                    <small
                        class="help-block text-red-500"
                        v-if="errors.email"
                        v-text="errors.email[0]" />
                    <small
                        class="help-block text-red-500"
                        v-if="errors.password"
                        v-text="errors.password[0]" />
                    <div class="flex items-center">
                        <input
                            type="password"
                            v-model="password"
                            ref="password"
                            class="input-text"
                            tabindex="1"
                            autofocus
                            @keydown.enter.prevent="submit" />
                        <button @click="submit" class="btn-primary rtl:mr-2 ltr:ml-2" v-text="__('Log in')" />
                    </div>
                </div>
            </div>
            </div>
        </modal>

    </div>

</template>

<script>
var counter;

export default {

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
            count: this.lifetime, // The timer used in vue
            remaining: this.lifetime, // The actual time remaining as per server responses
            errors: {},
            password: null,
            pinging: false,
            lastCount: moment(),
            isPageHidden: false,
        }
    },

    computed: {

        isWarning() {
            return this.count <= this.warnAt;
        },

        warningText() {
            return (this.remaining === 0)
                ? __('messages.session_expiry_logged_out_for_inactivity')
                : __('messages.session_expiry_logging_out_in_seconds', { seconds: this.remaining });
        },

        isUsingOauth() {
            return this.oauthProvider != null;
        }

    },

    created() {
        this.startCountdown();

        document.addEventListener('visibilitychange', () => this.isPageHidden = document.hidden, false);
    },

    watch: {

        count(count) {
            this.isShowingLogin = this.auth.enabled && this.remaining <= 0;

            // While we're in the warning period, we'll check every second so that any
            // activity in another tab is picked up and the count will get restarted.
            const withinWarningPeriod = count <= this.warnAt;

            // We keep track of the last time a count was made. It will be every second while
            // Javascript is being executed, but the count will have stopped if the computer
            // has been put to sleep. If it's been a while since the last count, we'll
            // also perform a timeout check. This will let things recalibrate.
            const secondsSinceLastCount = moment().diff(this.lastCount, 'seconds');
            const itsBeenAWhile = secondsSinceLastCount > 10;

            if (withinWarningPeriod || itsBeenAWhile) {
                this.ping().catch(e => {});
            }

            this.lastCount = moment();
        },

        isShowingLogin(showing, wasShowing) {
            if (showing && !wasShowing) this.updateCsrfToken();
        }

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

            return this.$axios.get(cp_url('session-timeout')).then(response => {
                this.count = this.remaining = response.data;
            }).catch(e => {
                if (e.response.status === 401) {
                    this.remaining = 0;
                    if (!this.auth.enabled) window.location = this.auth.redirect_to || '/';
                }
                throw e;
            }).finally(response => {
                this.pinging = false;
            });
        },

        updateCsrfToken() {
            return this.$axios.get(cp_url('auth/token')).then(response => {
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
            this.$axios.post(cp_url('auth/login'), {
                email: this.email,
                password: this.password
            }).then(response => {
                this.errors = {};
                this.password = null;
                this.isShowingLogin = false;
                this.$toast.success(__('Logged in'));
                this.restartCountdown();
                this.updateCsrfToken();
            }).catch(e => {
                if (e.response.status === 422) {
                    this.errors = e.response.data.errors;
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Something went wrong'))
                }
            });
        },

        extend() {
            this.$axios.get(cp_url('auth/extend')).then(response => {
                this.remaining = this.lifetime;
            });
        }

    }

}
</script>
