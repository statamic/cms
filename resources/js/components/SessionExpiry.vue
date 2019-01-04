<template>

    <div>

        <div v-if="isWarning" class="px-2 py-1 bg-red text-white">
            <span v-text="warningText" />
            <button v-if="remaining > 0" @click="extend">{{ __("Click here to extend your session.") }}</button>
        </div>

        <modal name="session-timeout-login" v-if="isShowingLogin" height="auto" width="500px">
            <div class="flex items-center p-3 bg-grey-lightest border-b text-center">
                {{ __('Enter your password to continue') }}
            </div>
            <div class="publish-fields">
                <div class="form-group">
                    <label v-text="__('Password')" />
                    <small
                        class="help-block text-red"
                        v-if="errors.email"
                        v-text="errors.email[0]" />
                    <small
                        class="help-block text-red"
                        v-if="errors.password"
                        v-text="errors.password[0]" />
                    <input
                        type="password"
                        v-model="password"
                        ref="password"
                        class="input-text"
                        @keydown.enter.prevent="submit" />
                </div>
                <div class="p-3 pt-0">
                    <button @click="submit" class="btn btn-primary" v-text="__('Submit')" />
                </div>
            </div>
        </modal>

    </div>

</template>

<script>
import axios from 'axios';

var counter;

export default {

    props: {
        warnAt: Number,
        lifetime: Number,
        email: String
    },

    data() {
        return {
            isShowingLogin: false,
            count: this.lifetime, // The timer used in vue
            remaining: this.lifetime, // The actual time remaining as per server responses
            errors: {},
            password: null,
            pinging: false,
            lastCount: moment()
        }
    },

    computed: {

        isWarning() {
            return this.count <= this.warnAt;
        },

        warningText() {
            return (this.remaining === 0)
                ? __('You have been logged out due to inactivity.')
                : __('You will be logged out in :seconds seconds due to inactivity.', { seconds: this.remaining });
        }

    },

    created() {
        this.startCountdown();
    },

    watch: {

        count(count) {
            if (this.remaining === 0) {
                this.stopCountdown();
                this.showLogin();
                return;
            }

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
        }

    },

    methods: {

        startCountdown() {
            counter = setInterval(() => {
                this.count--;
            }, 1000);
        },

        stopCountdown() {
            clearInterval(counter);
        },

        restartCountdown() {
            this.count = this.remaining = this.lifetime;
            this.startCountdown();
        },

        ping() {
            if (this.pinging) return Promise.resolve();

            this.pinging = true;

            return axios.get(cp_url('session-timeout')).then(response => {
                this.count = this.remaining = response.data;
            }).catch(e => {
                if (e.response.status === 401) this.remaining = 0;
                throw e;
            }).finally(response => {
                this.pinging = false;
            });
        },

        showLogin() {
            this.isShowingLogin = true;
        },

        updateCsrfToken() {
            return axios.get(cp_url('auth/token')).then(response => {
                const csrf = response.data;
                axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
                Statamic.csrfToken = csrf;
            });
        },

        submit() {
            this.updateCsrfToken().then(() => {
                this.login();
            });
        },

        login() {
            axios.post(cp_url('auth/login'), {
                email: this.email,
                password: this.password
            }).then(response => {
                this.errors = {};
                this.password = null;
                this.isShowingLogin = false;
                this.$notify.success(__('Logged in'));
                this.restartCountdown();
                this.updateCsrfToken();
            }).catch(e => {
                if (e.response.status === 422) {
                    this.errors = e.response.data.errors;
                    this.$notify.error(e.response.data.message);
                } else {
                    this.$notify.error(__('Something went wrong'), { timeout: 3000 })
                }
            });
        },

        extend() {
            axios.get(cp_url('auth/extend')).then(response => {
                this.remaining = this.lifetime;
            });
        }

    }

}
</script>
