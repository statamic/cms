<template>
    <div class="max-w-xl mx-auto rounded shadow bg-white">
        <div v-if="steps.length > 1" class="max-w-lg mx-auto pt-6 relative">
            <div class="wizard-steps">
                <a class="step" :class="{'complete': currentStep >= index}" v-for="(step, index) in steps" @click="goToStep(index)">
                    <div class="ball">{{ index+1 }}</div>
                    <div class="label">{{ step }}</div>
                </a>
            </div>
        </div>

        <!-- Step: User Info -->
        <div v-if="!completed && onUserInfoStep">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Create User') }}</h1>
                <p class="text-grey" v-text="__('messages.user_wizard_intro')" />
            </div>

            <!-- Email Address -->
            <div class="max-w-md mx-auto px-2 pb-5">
                <label class="font-bold text-base mb-sm" for="email">{{ __('Email Address') }}*</label>
                <input type="email" v-model="user.email" id="email" class="input-text" required autofocus tabindex="1">

                <div class="text-2xs text-red mt-1 flex items-center" v-if="userExists">
                    <svg-icon name="info-circle" class="h-4 w-4 mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('This user already exists.') }}
                </div>
                <div class="text-2xs text-grey-60 mt-1 flex items-center" v-else>
                    <svg-icon name="info-circle" class="h-4 w-4 mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.user_wizard_email_instructions') }}
                </div>
            </div>

            <!-- Name -->
            <div v-if="! separateNameFields" class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Name') }}</label>
                <input type="text" v-model="user.name" id="name" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="h-4 w-4 mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.user_wizard_name_instructions') }}
                </div>
            </div>

            <div v-else class="max-w-md mx-auto px-2 pb-7 flex space-x-4">
                <div class="flex-1">
                    <label class="font-bold text-base mb-sm" for="first_name">{{ __('First Name') }}</label>
                    <input type="text" v-model="user.first_name" id="first_name" class="input-text" tabindex="2">
                    <div class="text-2xs text-grey-60 mt-1 flex items-center">
                        <svg-icon name="info-circle" class="h-4 w-4 mr-sm flex items-center mb-px"></svg-icon>
                        {{ __('messages.user_wizard_name_instructions') }}
                    </div>
                </div>

                <div class="flex-1">
                    <label class="font-bold text-base mb-sm" for="last_name">{{ __('Last Name') }}</label>
                    <input type="text" v-model="user.last_name" id="last_name" class="input-text" tabindex="2">
                </div>
            </div>
        </div>

        <!-- Step: Roles & Groups -->
        <div v-if="!completed && onPermissionStep" class="max-w-md mx-auto px-2 pb-2">
            <div class="py-6 text-center">
                <h1 class="mb-3">{{ __('Roles & Groups') }}</h1>
                <p class="text-grey" v-text="__('messages.user_wizard_roles_groups_intro')" />
            </div>

            <!-- Super Admin -->
             <div class="pb-5" v-if="canCreateSupers">
                <div class="flex items-center">
                    <toggle-input v-model="user.super" />
                    <label class="font-bold ml-1">{{ __('Super Admin') }}</label>
                </div>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="h-4 w-4 mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.user_wizard_super_admin_instructions') }}
                </div>
            </div>

            <!-- Roles -->
            <div class="pb-5" v-if="! user.super && canAssignRoles">
                <label class="font-bold text-base mb-sm" for="role">{{ __('Roles') }}</label>
                <publish-field-meta
                    :config="{ handle: 'user.roles', type: 'user_roles' }"
                    :initial-value="user.roles">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            handle="user.roles"
                            :config="{ type: 'user_roles', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            @input="user.roles = $event" />
                    </div>
                </publish-field-meta>
            </div>

            <!-- Groups -->
            <div class="pb-5" v-if="! user.super && canAssignGroups">
                <label class="font-bold text-base mb-sm" for="group">{{ __('Groups') }}</label>
                <publish-field-meta
                    :config="{ handle: 'user.groups', type: 'user_groups' }"
                    :initial-value="user.groups">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            handle="user.groups"
                            :config="{ type: 'user_groups', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            @input="user.groups = $event" />
                    </div>
                </publish-field-meta>
            </div>
        </div>

        <!-- Step: Invitation -->
        <div v-if="!completed && onInvitationStep">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Invitation') }}</h1>
                <p class="text-grey" v-text="__('messages.user_wizard_invitation_intro')" />
            </div>

            <!-- Send Email? -->
            <div class="max-w-md mx-auto px-2 mb-3 flex items-center">
                <toggle-input v-model="invitation.send" />
                <label class="font-bold ml-1">{{ __('Send Email Invitation') }}</label>
            </div>

            <div class="max-w-lg mx-auto bg-grey-10 py-5 mb-7 border rounded-lg " v-if="invitation.send">
                <!-- Subject Line -->
                <div class="max-w-md mx-auto px-2 pb-5">
                    <label class="font-bold text-base mb-sm" for="email">{{ __('Email Subject') }}</label>
                    <input type="text" v-model="invitation.subject" class="input-text bg-white">
                </div>

                <!-- Email Content -->
                <div class="max-w-md mx-auto px-2">
                    <label class="font-bold text-base mb-sm" for="email">{{ __('Email Content') }}</label>
                    <textarea
                        class="input-text min-h-48 p-2 bg-white"
                        v-model="invitation.message"
                        v-elastic
                    />
                </div>
            </div>

            <!-- Copy Pasta -->
            <div class="max-w-md mx-auto px-2 pb-7" v-else>
                <p class="mb-1" v-html="__('messages.user_wizard_invitation_share_before', { email: user.email })" />
            </div>
        </div>

        <!-- Post creation -->
        <div v-if="completed">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('User created') }}</h1>
                <p class="text-grey" v-html="__('messages.user_wizard_account_created')" />
            </div>

            <!-- Copy Pasta -->
            <div class="max-w-md mx-auto px-2 pb-5">
                <p class="mb-1" v-html="__('messages.user_wizard_invitation_share', { email: user.email })" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-5">
                <label class="font-bold text-base mb-sm" for="email">{{ __('Activation URL') }}</label>
                <input type="text" readonly class="input-text" onclick="this.select()" :value="activationUrl" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="email">{{ __('Email Address') }}</label>
                <input type="text" readonly class="input-text" onclick="this.select()" :value="user.email" />
            </div>
        </div>

        <div class="border-t p-2">
            <div class="max-w-md mx-auto flex items-center justify-center">
                <button tabindex="3" class="btn mx-2 w-32" @click="previous" v-if="! completed && ! onFirstStep">
                    &larr; {{ __('Previous')}}
                </button>
                <button tabindex="4" class="btn mx-2 w-32" :disabled="! canContinue" @click="next" v-if="! completed && ! onLastStep">
                    {{ __('Next')}} &rarr;
                </button>
                <button tabindex="4" class="btn-primary mx-2" @click="submit" v-if="! completed && onLastStep">
                    {{ finishButtonText }}
                </button>
                <a :href="usersIndexUrl" class="btn mx-2" v-if="completed">
                    {{ __('Back to Users') }}
                </a>
                <a :href="usersCreateUrl" class="btn-primary mx-2" v-if="completed">
                    {{ __('Create Another') }}
                </a>
            </div>
        </div>
    </div>
</template>

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
    },

    data() {
        return {
            user: {
                email: null,
                super: this.canCreateSupers,
                roles: [],
                groups: []
            },
            invitation: {
                send: this.canSendInvitation,
                subject: __('messages.user_wizard_invitation_subject', { site: window.location.hostname }),
                message: __('messages.user_wizard_invitation_body', { site: window.location.hostname, expiry: this.activationExpiry }),
            },
            userExists: false,
            completed: false,
            activationUrl: null,
            editUrl: null,
        }
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
        isValidEmail() {
            return this.user.email && isEmail(this.user.email)
        }
    },

    methods: {
        canGoToStep(step) {
            if (this.completed) return false;

            if (step >= 1) {
                return this.isValidEmail && ! this.userExists;
            }

            return true;
        },
        checkIfUserExists() {
            this.$axios.post(cp_url('user-exists'), {email: this.user.email}).then(response => {
                this.userExists = response.data.exists
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        },
        submit() {
            let payload = {...this.user, invitation: this.invitation};

            this.$axios.post(this.route, payload).then(response => {
                if (this.invitation.send) {
                    window.location = response.data.redirect;
                } else {
                    this.completed = true;
                    this.editUrl = response.data.redirect;
                    this.activationUrl = response.data.activationUrl;
                }
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    },

    watch: {
        'user.email': function(email) {
            if (this.isValidEmail) {
                this.checkIfUserExists()
            }
        }
    },

    mounted() {
        this.$keys.bindGlobal(['command+return'], e => {
            this.next();
        });

        this.$keys.bindGlobal(['command+delete'], e => {
            this.previous();
        });
    }

}
</script>
