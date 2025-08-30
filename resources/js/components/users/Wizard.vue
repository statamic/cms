<template>
    <div class="mx-auto mt-4 space-y-3 lg:space-y-6 max-w-3xl">
        <!-- Wizard Steps (left as-is per user request) -->
        <div v-if="steps.length > 1" class="relative mx-auto max-w-2xl pt-16">
            <div class="wizard-steps">
                <button
                    class="step"
                    :class="{ complete: currentStep >= index }"
                    v-for="(step, index) in steps"
                    @click="goToStep(index)"
                >
                    <div class="ball">{{ index + 1 }}</div>
                    <div class="label">{{ step }}</div>
                </button>
            </div>
        </div>

        <!-- Step: User Info -->
        <div v-if="!completed && onUserInfoStep">
            <header class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
                <ui-heading size="2xl" :level="1" icon="users" :text="__('Create User')" class="justify-center" />
                <ui-subheading class="mt-6" size="lg" :text="__('messages.user_wizard_intro')" />
            </header>

            <ui-card-panel :heading="__('User Information')">
                <ui-publish-container
                    ref="container"
                    :blueprint="blueprint"
                    v-model="values"
                    :meta="meta"
                    :track-dirty-state="false"
                    :errors="errors"
                >
                    <ui-publish-fields-provider :fields="fields">
                        <ui-publish-fields />
                    </ui-publish-fields-provider>
                </ui-publish-container>
            </ui-card-panel>
        </div>

        <!-- Step: Roles & Groups -->
        <div v-if="!completed && onPermissionStep">
            <header class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
                <ui-heading size="2xl" :level="1" icon="users" :text="__('Roles & Groups')" class="justify-center" />
                <ui-subheading class="mt-6" size="lg" :text="__('messages.user_wizard_roles_groups_intro')" />
            </header>

            <ui-card-panel :heading="__('Permissions')">
                <div class="space-y-8">
                    <!-- Super Admin -->
                    <div v-if="canCreateSupers">
                        <div class="flex items-center gap-2">
                            <ui-switch v-model="user.super" id="super" />
                            <label for="super" v-text="__('Super Admin')" />
                        </div>
                        <ui-description class="mt-3 flex items-center gap-2">
                            <ui-icon name="info-square" class="size-4" />
                            <span>{{ __('messages.user_wizard_super_admin_instructions') }}</span>
                        </ui-description>
                    </div>

                    <!-- Roles -->
                    <ui-publish-container
                        v-if="!user.super"
                        :blueprint="permissionsBlueprint"
                        :meta="meta"
                        :model-value="{ roles: user.roles, groups: user.groups }"
                        :track-dirty-state="false"
                        @update:model-value="(event) => {
                            user.roles = event.roles;
                            user.groups = event.groups;
                        }"
                    >
                        <ui-publish-fields-provider :fields="permissionsBlueprint.tabs[0].sections[0].fields">
                            <ui-publish-fields />
                        </ui-publish-fields-provider>
                    </ui-publish-container>
                </div>
            </ui-card-panel>
        </div>

        <!-- Step: Invitation -->
        <div v-if="!completed && onInvitationStep">
            <header class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
                <ui-heading size="2xl" :level="1" icon="mail" :text="__('Invitation')" class="justify-center" />
                <ui-subheading class="mt-6" size="lg" :text="__('messages.user_wizard_invitation_intro')" />
            </header>

            <ui-card-panel :heading="__('Invitation Settings')">
                <div class="space-y-4">
                    <!-- Send Email? -->
                    <div class="flex items-center gap-2">
                        <ui-switch v-model="invitation.send" id="send_email_invitation" />
                        <label for="send_email_invitation" v-text="__('Send Email Invitation')" />
                    </div>

                    <div
                        class="rounded-lg border bg-gray-100 p-6 dark:border-gray-700 dark:bg-gray-800"
                        v-if="invitation.send"
                    >
                        <!-- Subject Line -->
                        <div class="pb-10">
                            <ui-label for="invitation_subject" :text="__('Email Subject')" />
                            <ui-input
                                type="text"
                                v-model="invitation.subject"
                                id="invitation_subject"
                            />
                        </div>

                        <!-- Email Content -->
                        <div>
                            <ui-label for="invitation_message" :text="__('Email Content')" />
                            <ui-textarea
                                min-h-40
                                id="invitation_message"
                                v-model="invitation.message"
                                v-elastic
                            />
                        </div>
                    </div>

                    <!-- Copy Pasta -->
                    <div v-else>
                        <ui-description v-html="__('messages.user_wizard_invitation_share_before', { email: values.email })" />
                    </div>
                </div>
            </ui-card-panel>
        </div>

        <!-- Post creation -->
        <div v-if="completed">
            <header class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
                <ui-heading size="2xl" :level="1" icon="users" :text="__('User created')" class="justify-center" />
                <ui-subheading class="mt-6" size="lg" :text="__('messages.user_wizard_account_created')" />
            </header>

            <ui-card-panel :heading="__('User Details')">
                <div class="space-y-4">
                    <ui-description v-html="__('messages.user_wizard_invitation_share', { email: values.email })" />
                    <div>
                        <ui-label for="activation_url" :text="__('Activation URL')" />
                        <ui-input
                            readonly
                            copyable
                            :value="activationUrl"
                            id="activation_url"
                        />
                    </div>
                    <div>
                        <ui-label for="email" :text="__('Email Address')" />
                        <ui-input
                            readonly
                            copyable
                            :value="values.email"
                            id="email"
                        />
                    </div>
                </div>
            </ui-card-panel>
        </div>

        <!-- Footer -->
        <footer class="flex justify-center py-3">
            <div class="flex items-center space-x-4">
                <ui-button
                    v-if="!completed && !onFirstStep"
                    variant="default"
                    @click="previous"
                    :text="__('Previous')"
                />
                <ui-button
                    v-if="onUserInfoStep"
                    variant="primary"
                    @click="nextStep"
                    :text="__('Next')"
                />
                <ui-button
                    v-if="!onUserInfoStep && !completed && !onLastStep"
                    variant="primary"
                    :disabled="!canContinue"
                    @click="nextStep"
                    :text="__('Next')"
                />
                <ui-button
                    v-if="!completed && onLastStep"
                    variant="primary"
                    @click="submit"
                    :text="finishButtonText"
                />
                <ui-button
                    v-if="completed"
                    variant="default"
                    :href="usersIndexUrl"
                    :text="__('Back to Users')"
                />
                <ui-button
                    v-if="completed"
                    variant="primary"
                    :href="usersCreateUrl"
                    :text="__('Create Another')"
                />
            </div>
        </footer>
    </div>
</template>

<script>
// Yer a wizard Harry

import isEmail from 'validator/lib/isEmail';
import HasWizardSteps from '../HasWizardSteps.js';
import RelationshipFieldtype from '@/components/fieldtypes/relationship/RelationshipFieldtype.vue';

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
        permissionsBlueprint() {
            let fields = [];

            if (this.canAssignRoles) {
                fields.push({ handle: 'roles', component: 'relationship', type: 'user_roles', display: __('Roles'), mode: 'select' });
            }

            if (this.canAssignGroups) {
                fields.push({ handle: 'groups', component: 'relationship', type: 'user_groups', display: __('Groups'), mode: 'select' });
            }

            return { tabs: [{sections: [{ fields }]}]};
        },
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
