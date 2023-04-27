<template>
    <popover placement="bottom" ref="popper">
        <button
            slot="trigger"
            class="btn"
            v-text="__('Change Password')"
        />
        <div class="saving flex justify-center text-center" v-if="saving">
            <loading-graphic :text="__('Saving')" />
        </div>
        <div class="publish-fields p-2 pb-0 w-96">
            <form-group
                handle="password"
                :display="__('Current Password')"
                v-model="currentPassword"
                :errors="errors.current_password"
                class="p-0 mb-3"
                :config="{ input_type: this.inputType }"
            />
            <form-group
                handle="password"
                :display="__('Password')"
                v-model="password"
                :errors="errors.password"
                class="p-0 mb-3"
                :config="{ input_type: this.inputType }"
            />
            <form-group
                handle="confirmation"
                :display="__('Password Confirmation')"
                v-model="confirmation"
                class="p-0 mb-3"
                :config="{ input_type: this.inputType }"
            />
        </div>
        <div class="flex items-center bg-grey-21 border-t rounded-b px-2 py-1">
            <button class="btn-primary" @click.prevent="save">{{ __('Change Password') }}</button>
            <label class="ml-2">
                <input type="checkbox" v-model="reveal" />
                {{ __('Reveal Password') }}
            </label>
        </div>
    </popover>

</template>

<script>
export default {

    props: {
        saveUrl: String,
    },

    data() {
        return {
            saving: false,
            error: null,
            errors: {},
            currentPassword: null,
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
                current_password: this.currentPassword,
                password: this.password,
                password_confirmation: this.confirmation
            }).then(response => {
                this.$toast.success(__('Password changed'));
                this.$refs.popper.close();
                this.saving = false;
                this.password = null;
                this.currentPassword = null;
                this.confirmation = null;
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                    this.saving = false;
                } else {
                    this.$toast.error(__('Unable to change password'));
                    this.saving = false;
                }
            })
        }

    }

}
</script>
