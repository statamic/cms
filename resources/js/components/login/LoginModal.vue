<template>

    <div>

        <modal :show="show" class="modal-login" :shake="hasErrors">
            <template slot="header">
                {{ __('Log in to continue') }}
            </template>
            <template slot="body">
                <div class="mb-2">
                    <label :class="{ 'text-red': errors.password.length }">{{ __('Password for :username', { username: this.username }) }} <i class="required">*</i></label>
                    <input type="password" name="password" class="input-text" v-model="password" ref="password" @keydown.enter.prevent="submit" />
                    <small class="block text-red mt-1" v-if="errors.username.length">{{ errors.username[0] }}</small>
                    <small class="block text-red mt-1" v-if="errors.password.length">{{ errors.password[0] }}</small>
                </div>
            </template>
            <template slot="footer">
                <button @click.prevent="submit" class="btn-primary">{{ __('Submit') }}</button>
            </template>
        </modal>

    </div>

</template>

<script>
export default {

    props: ['username'],

    data() {
        return {
            show: true,
            errors: [],
            password: null
        }
    },

    mounted() {
        this.$http.get(cp_url('auth/token')).success(response => {
            Vue.http.headers.common['X-CSRF-TOKEN'] = response;
        });

        this.$refs.password.focus();
    },

    computed: {
        hasErrors() {
            return ! _.isEmpty(this.errors);
        }
    },

    methods: {

        submit() {
            this.errors = []; // reset errors

            let payload = {
                username: this.username,
                password: this.password
            };

            this.$axios.post(cp_url('auth/login'), payload).then(response => {
                this.errors = [];
                this.$toast.success(__('Logged in'));
                this.show = false;
                this.$emit('closed');
            }).catch(response => {
                this.errors = response;
            });
        }

    }

}
</script>
