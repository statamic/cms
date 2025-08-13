<template>
    <Modal v-model:open="open" :title="__('Change Password')">
        <template #trigger>
            <Button v-text="__('Change Password')" />
        </template>

        <div class="publish-fields">
            <Field
                v-if="requiresCurrentPassword"
                class="form-group"
                :label="__('Current Password')"
                :errors="errors.current_password"
            >
                <Input v-model="currentPassword" type="password" viewable />
            </Field>

            <Field
                class="form-group"
                :label="__('Password')"
                :errors="errors.password"
            >
                <Input v-model="password" type="password" viewable />
            </Field>

            <Field
                class="form-group"
                :label="__('Password Confirmation')"
            >
                <Input v-model="confirmation" type="password" viewable />
            </Field>
        </div>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose>
                    <Button text="Cancel" variant="ghost" />
                </ModalClose>
                <Button :text="__('Change Password')" variant="primary" @click="save" :disabled="saving" />
            </div>
        </template>
    </Modal>
</template>

<script>
import { Button, Modal, ModalClose, Switch, Field, Input } from '@statamic/ui';
import { requireElevatedSessionIf } from '@statamic/components/elevated-sessions';

export default {
    components: {
        Button,
        Modal,
        ModalClose,
        Switch,
        Field,
        Input
    },

    props: {
        saveUrl: String,
        requiresCurrentPassword: Boolean,
    },

    data() {
        return {
            saving: false,
            errors: {},
            currentPassword: null,
            password: null,
            confirmation: null,
            open: false,
        };
    },

    methods: {
        clearErrors() {
            this.errors = {};
        },

        save() {
            requireElevatedSessionIf(!this.requiresCurrentPassword)
                .then(() => this.performSaveRequest())
                .catch(() => {});
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
                    this.open = false;
                    this.saving = false;
                    this.password = null;
                    this.currentPassword = null;
                    this.confirmation = null;
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;
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
