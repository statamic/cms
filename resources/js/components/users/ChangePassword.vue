<template>

    <div>
        <popper ref="popper" trigger="click" :append-to-body="true" :options="{ placement: 'left-start' }">
            <div class="popover w-96"><div class="popover-inner p-2">
                <div class="saving flex justify-center text-center" v-if="saving">
                    <loading-graphic :text="__('Saving')" />
                </div>
                <div class="publish-fields">
                    <form-group
                        handle="password"
                        :display="__('Password')"
                        v-model="password"
                        :errors="errors.password"
                        class="p-0 mb-3"
                        :config="{ html_type: this.inputType }"
                    />
                    <form-group
                        handle="confirmation"
                        :display="__('Password Confirmation')"
                        v-model="confirmation"
                        class="p-0 mb-3"
                        :config="{ html_type: this.inputType }"
                    />
                </div>
                <div class="flex items-center">
                    <button class="btn-primary" @click.prevent="save">{{ __('Save') }}</button>
                    <label class="ml-2">
                        <input type="checkbox" v-model="reveal" />
                        {{ __('Reveal Password') }}
                    </label>
                </div>
            </div></div>

            <button
                slot="reference"
                class="btn"
                v-text="__('Change Password')"
            />
        </popper>
    </div>

</template>

<script>
import Popper from 'vue-popperjs';

export default {

    components: {
        Popper
    },

    props: {
        saveUrl: String,
    },

    data() {
        return {
            saving: false,
            error: null,
            errors: {},
            password: null,
            confirmation: null,
            reveal: false
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        inputType() {
            return this.reveal ? 'text' : 'password';
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();
            this.saving = true;

            this.$axios.patch(this.saveUrl, {
                password: this.password,
                password_confirmation: this.confirmation
            }).then(response => {
                this.$toast.success(__('Password changed'));
                this.$refs.popper.doClose();
                this.saving = false;
                this.password = null;
                this.confirmation = null;
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                    this.saving = false;
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            })
        }

    }

}
</script>
