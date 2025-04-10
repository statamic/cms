<template>
    <popover placement="bottom" ref="popper">
        <button slot="trigger" class="btn" v-text="__('Change Password')" />
        <div class="saving-overlay flex justify-center text-center" v-if="saving">
            <loading-graphic :text="__('Saving')" />
        </div>
        <div class="publish-fields w-96 p-4 pb-0">
            <form-group
                v-if="requiresCurrentPassword"
                handle="password"
                :display="__('Current Password')"
                v-model="currentPassword"
                :errors="errors.current_password"
                class="mb-6 p-0"
                :config="{ input_type: this.inputType }"
            />
            <form-group
                handle="password"
                :display="__('Password')"
                v-model="password"
                :errors="errors.password"
                class="mb-6 p-0"
                :config="{ input_type: this.inputType }"
            />
            <form-group
                handle="confirmation"
                :display="__('Password Confirmation')"
                v-model="confirmation"
                class="mb-6 p-0"
                :config="{ input_type: this.inputType }"
            />
        </div>
        <div class="bg-gray-21 flex items-center rounded-b border-t px-4 py-2 dark:border-dark-900 dark:bg-dark-575">
            <button class="btn-primary" @click.prevent="save">{{ __('Change Password') }}</button>
            <label class="ltr:ml-4 rtl:mr-4">
                <input type="checkbox" v-model="reveal" />
                {{ __('Reveal Password') }}
            </label>
        </div>
    </popover>
</template>

<script>
import HasElevatedSession from '@statamic/mixins/HasElevatedSession.js';

export default {
    mixins: [HasElevatedSession],

    props: {
        saveUrl: String,
        requiresCurrentPassword: Boolean,
    },

    data() {
        return {
            saving: false,
            error: null,
            errors: {},
            currentPassword: null,
            password: null,
            confirmation: null,
            reveal: false,
        };
    },

    computed: {
        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        inputType() {
            return this.reveal ? 'text' : 'password';
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.requireElevatedSession().then(() => this.performSaveRequest());
        },

        performSaveRequest() {
            this.clearErrors();
            this.saving = true;

            this.$axios
                .patch(this.saveUrl, {
                    current_password: this.currentPassword,
                    password: this.password,
                    password_confirmation: this.confirmation,
                })
                .then((response) => {
                    this.$toast.success(__('Password changed'));
                    this.$refs.popper.close();
                    this.saving = false;
                    this.password = null;
                    this.currentPassword = null;
                    this.confirmation = null;
                })
                .catch((e) => {
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
                });
        },
    },
};
</script>
