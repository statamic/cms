<template>
    <div class="card mx-auto max-w-xl">
        <div v-if="steps.length > 1" class="relative mx-auto max-w-lg pt-16">
            <div class="wizard-steps">
                <a
                    class="step"
                    :class="{ complete: currentStep >= index }"
                    v-for="(step, index) in steps"
                    @click="goToStep(index)"
                >
                    <div class="ball">{{ index + 1 }}</div>
                    <div class="label">{{ step }}</div>
                </a>
            </div>
        </div>

        <!-- Step: User Info -->
        <div v-if="!completed && onUserInfoStep">
            <div class="mx-auto max-w-md px-4 py-16 text-center">
                <h1 class="mb-6">{{ __('Create User') }}</h1>
                <p class="text-gray dark:text-dark-150" v-text="__('messages.user_wizard_intro')" />
            </div>

            <publish-container
                ref="container"
                name="userwizard"
                :blueprint="blueprint"
                :values="values"
                :meta="meta"
                :track-dirty-state="false"
                class="mx-auto -mt-6 max-w-md px-4 py-0 pb-20"
                @updated="values = $event"
                v-slot="{ setFieldValue, setFieldMeta }"
            >
                <div>
                    <div class="-mx-6">
                        <publish-fields :fields="fields" @updated="setFieldValue" @meta-updated="setFieldMeta" />
                    </div>
                </div>
            </publish-container>
        </div>

        <!-- Step: Roles & Groups -->
        <div v-if="!completed && onPermissionStep" class="mx-auto max-w-md px-4 pb-4">
            <div class="py-16 text-center">
                <h1 class="mb-6">{{ __('Roles & Groups') }}</h1>
                <p class="text-gray dark:text-dark-150" v-text="__('messages.user_wizard_roles_groups_intro')" />
            </div>

            <!-- Super Admin -->
            <div class="pb-10" v-if="canCreateSupers">
                <div class="flex items-center">
                    <toggle-input v-model="user.super" id="super" />
                    <label class="font-bold ltr:ml-2 rtl:mr-2" for="super">{{ __('Super Admin') }}</label>
                </div>
                <div
                    class="mt-2 flex items-center space-x-1 text-2xs text-gray-600 dark:text-dark-150 rtl:space-x-reverse"
                >
                    <svg-icon name="info-circle" class="mb-px flex h-4 w-4 items-center"></svg-icon>
                    <span>{{ __('messages.user_wizard_super_admin_instructions') }}</span>
                </div>
            </div>

            <!-- Roles -->
            <div class="pb-10" v-if="!user.super && canAssignRoles">
                <label class="mb-1 text-base font-bold" for="role">{{ __('Roles') }}</label>
                <publish-field-meta
                    :config="{ handle: 'user.roles', type: 'user_roles' }"
                    :initial-value="user.roles"
                    v-slot="{ meta, value, loading, updateMeta }"
                >
                    <div>
                        <relationship-fieldtype
                            v-if="!loading"
                            handle="user.roles"
                            :config="{ type: 'user_roles', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            @input="user.roles = $event"
                            @meta-updated="updateMeta"
                        />
                    </div>
                </publish-field-meta>
            </div>

            <!-- Groups -->
            <div class="pb-10" v-if="!user.super && canAssignGroups">
                <label class="mb-1 text-base font-bold" for="group">{{ __('Groups') }}</label>
                <publish-field-meta
                    :config="{ handle: 'user.groups', type: 'user_groups' }"
                    :initial-value="user.groups"
                    v-slot="{ meta, value, loading, updateMeta }"
                >
                    <div>
                        <relationship-fieldtype
                            v-if="!loading"
                            handle="user.groups"
                            :config="{ type: 'user_groups', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            @input="user.groups = $event"
                            @meta-updated="updateMeta"
                        />
                    </div>
                </publish-field-meta>
            </div>
        </div>

        <!-- Step: Invitation -->
        <div v-if="!completed && onInvitationStep">
            <div class="mx-auto max-w-md px-4 py-16 text-center">
                <h1 class="mb-6">{{ __('Invitation') }}</h1>
                <p class="text-gray dark:text-dark-150" v-text="__('messages.user_wizard_invitation_intro')" />
            </div>

            <!-- Send Email? -->
            <div class="mx-auto mb-6 flex max-w-md items-center px-4">
                <toggle-input v-model="invitation.send" id="send_email_invitation" />
                <label class="font-bold ltr:ml-2 rtl:mr-2" for="send_email_invitation">{{
                    __('Send Email Invitation')
                }}</label>
            </div>

            <div
                class="mx-auto mb-20 max-w-lg rounded-lg border bg-gray-100 py-10 dark:border-dark-900 dark:bg-dark-650"
                v-if="invitation.send"
            >
                <!-- Subject Line -->
                <div class="mx-auto max-w-md px-4 pb-10">
                    <label class="mb-1 text-base font-bold" for="invitation_subject">{{ __('Email Subject') }}</label>
                    <input
                        type="text"
                        v-model="invitation.subject"
                        class="input-text bg-white dark:bg-dark-700"
                        id="invitation_subject"
                    />
                </div>

                <!-- Email Content -->
                <div class="mx-auto max-w-md px-4">
                    <label class="mb-1 text-base font-bold" for="invitation_message">{{ __('Email Content') }}</label>
                    <textarea
                        class="input-text min-h-40 bg-white p-4 dark:bg-dark-700"
                        id="invitation_message"
                        v-model="invitation.message"
                        v-elastic
                    />
                </div>
            </div>

            <!-- Copy Pasta -->
            <div class="mx-auto max-w-md px-4 pb-20" v-else>
                <p class="mb-2" v-html="__('messages.user_wizard_invitation_share_before', { email: values.email })" />
            </div>
        </div>

        <!-- Post creation -->
        <div v-if="completed">
            <div class="mx-auto max-w-md px-4 py-16 text-center">
                <h1 class="mb-6">{{ __('User created') }}</h1>
                <p class="text-gray" v-html="__('messages.user_wizard_account_created')" />
            </div>

            <!-- Copy Pasta -->
            <div class="mx-auto max-w-md px-4 pb-10">
                <p class="mb-2" v-html="__('messages.user_wizard_invitation_share', { email: values.email })" />
            </div>
            <div class="mx-auto max-w-md px-4 pb-10">
                <label class="mb-1 text-base font-bold" for="activation_url">{{ __('Activation URL') }}</label>
                <input
                    type="text"
                    readonly
                    class="input-text"
                    onclick="this.select()"
                    :value="activationUrl"
                    id="activation_url"
                />
            </div>
            <div class="mx-auto max-w-md px-4 pb-20">
                <label class="mb-1 text-base font-bold" for="email">{{ __('Email Address') }}</label>
                <input
                    type="text"
                    readonly
                    class="input-text"
                    onclick="this.select()"
                    :value="values.email"
                    id="email"
                />
            </div>
        </div>

        <div class="border-t p-4 dark:border-dark-900">
            <div class="mx-auto flex max-w-md items-center justify-center">
                <button tabindex="3" class="btn mx-4 w-32" @click="previous" v-if="!completed && !onFirstStep">
                    <span v-html="direction === 'ltr' ? '&larr;' : '&rarr;'"></span> {{ __('Previous') }}
                </button>
                <button tabindex="4" class="btn mx-4 w-32" @click="nextStep" v-if="onUserInfoStep">
                    {{ __('Next') }} <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                </button>
                <button
                    tabindex="4"
                    class="btn mx-4 w-32"
                    :disabled="!canContinue"
                    @click="nextStep"
                    v-if="!onUserInfoStep && !completed && !onLastStep"
                >
                    {{ __('Next') }} <span v-html="direction === 'ltr' ? '&rarr;' : '&larr;'"></span>
                </button>
                <button tabindex="4" class="btn-primary mx-4" @click="submit" v-if="!completed && onLastStep">
                    {{ finishButtonText }}
                </button>
                <a :href="usersIndexUrl" class="btn mx-4" v-if="completed">
                    {{ __('Back to Users') }}
                </a>
                <a :href="usersCreateUrl" class="btn-primary mx-4" v-if="completed">
                    {{ __('Create Another') }}
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.publish-fields .form-group .field-inner > label) {
    /* TODO: Remove @apply */
    /* @apply mb-1 text-base font-bold;
    & + .help-block {
        @apply -mt-1 !important;
    } */
}
</style>

<script>
// Yer a wizard Harry

import isEmail from 'validator/lib/isEmail';
import HasWizardSteps from '../HasWizardSteps.js';

export default {
    mixins: [HasWizardSteps],

    props: {
        route: { type: String },
        usersCreateUrl: { type: String },
        usersIndexUrl: { type: String },
        canCreateSupers: { type: Boolean },
        canAssignRoles: { type: Boolean },
        canAssignGroups: { type: Boolean },
        activationExpiry: { type: Number },
        separateNameFields: { type: Boolean },
        canSendInvitation: { type: Boolean },
        blueprint: { type: Object },
        initialValues: { type: Object },
        fields: { type: Array },
        meta: { type: Object },
    },

    data() {
        return {
            user: {
                super: this.canCreateSupers,
                roles: [],
                groups: [],
            },
            invitation: {
                send: this.canSendInvitation,
                subject: __('messages.user_wizard_invitation_subject', { site: window.location.hostname }),
                message: __n('messages.user_wizard_invitation_body', this.activationExpiry, {
                    site: window.location.hostname,
                    expiry: this.activationExpiry,
                }),
            },
            userExists: false,
            completed: false,
            activationUrl: null,
            editUrl: null,
            errors: {},
            error: null,
            values: this.initialValues,
        };
    },

    computed: {
        steps() {
            let steps = [__('User Information')];
            if (this.canAssignPermissions) steps.push(__('Roles & Groups'));
            if (this.canSendInvitation) steps.push(__('Customize Invitation'));

            return steps;
        },
        canAssignPermissions() {
            return this.canAssignRoles || this.canAssignGroups;
        },
        onUserInfoStep() {
            return this.onFirstStep;
        },
        onPermissionStep() {
            return this.canAssignPermissions ? this.currentStep === 1 : false;
        },
        onInvitationStep() {
            return this.canAssignPermissions ? this.currentStep === 2 : this.currentStep === 1;
        },
        finishButtonText() {
            return this.invitation.send ? __('Create and Send Email') : __('Create User');
        },
        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    methods: {
        canGoToStep(step) {
            // If we've created the user, you shouldn't be allowed to go back anywhere.
            if (this.completed) return false;

            // You can go back to the first step from anywhere.
            if (step === 0) return true;

            // Otherwise, you can only go to a step if the first one is complete/valid.
            return this.valid;
        },
        checkIfUserExists(email) {
            this.$axios
                .post(cp_url('user-exists'), { email })
                .then((response) => {
                    this.userExists = response.data.exists;
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
        nextStep() {
            if (this.onUserInfoStep) {
                return this.submit(true)
                    .then(this.next)
                    .catch(() => {});
            }

            this.next();
        },
        submit(validateOnly) {
            let payload = { ...this.user, ...this.values, invitation: this.invitation };

            if (validateOnly === true) {
                payload._validate_only = true;
            }

            this.clearErrors();

            return this.$axios
                .post(this.route, payload)
                .then((response) => {
                    this.valid = true;

                    if (payload._validate_only) {
                        return;
                    }

                    if (this.invitation.send) {
                        window.location = response.data.redirect;
                    } else {
                        this.completed = true;
                        this.editUrl = response.data.redirect;
                        this.activationUrl = response.data.activationUrl;
                    }
                })
                .catch((e) => {
                    this.handleAxiosError(e);
                    throw e;
                });
        },
        handleAxiosError(e) {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.valid = false;
                this.$toast.error(message);
            } else {
                this.$toast.error(__(e.response.data.message));
            }
        },
        clearErrors() {
            this.error = null;
            this.errors = {};
        },
    },

    watch: {
        'values.email': function (email) {
            if (email && isEmail(email)) this.checkIfUserExists(email);
        },

        userExists(exists) {
            let emailErrors = this.errors.email || [];
            const error = __('statamic::validation.unique');
            if (exists) {
                emailErrors.push(error);
            } else {
                emailErrors = emailErrors.filter((error) => error !== error);
            }
            this.errors = { ...this.errors, email: [...new Set(emailErrors)] };
        },

        errors: function (errors) {
            if (this.onUserInfoStep) this.$refs.container.store.setErrors(errors);
        },
    },

    mounted() {
        this.$keys.bindGlobal(['command+return'], (e) => {
            this.next();
        });

        this.$keys.bindGlobal(['command+delete'], (e) => {
            this.previous();
        });
    },
};
</script>
