<template>
    <div class="max-w-xl mx-auto rounded shadow bg-white">
        <div class="max-w-lg mx-auto pt-6 relative">
            <div class="wizard-steps">
                <a class="step" :class="{'complete': currentStep >= index}" v-for="(step, index) in steps" @click="goToStep(index)">
                    <div class="ball">{{ index+1 }}</div>
                    <div class="label">{{ step }}</div>
                </a>
            </div>
        </div>

        <!-- Step 1 -->
        <div v-if="currentStep === 0">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Create a New User') }}</h1>
                <p class="text-grey">Users can be assigned to roles that customize their permissions, access, and abilities throughout the Control Panel.</p>
            </div>

            <!-- Email Address -->
            <div class="max-w-md mx-auto px-2 pb-5">
                <label class="font-bold text-base mb-sm" for="email">Email Address*</label>
                <input type="email" v-model="user.email" id="email" class="input-text" required autofocus tabindex="1">

                <div class="text-2xs text-red mt-1 flex items-center" v-if="userExists">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    This user already exists.
                </div>
                <div class="text-2xs text-grey-60 mt-1 flex items-center" v-else>
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    The email address also serves as a username and must be unique
                </div>
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name</label>
                <input type="text" v-model="user.name" id="name" class="input-text" autofocus tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    You can leave the name blank if you want to let the user fill it in.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1" class="max-w-md mx-auto px-2 pb-2">
            <div class="py-6 text-center">
                <h1 class="mb-3">{{ __('Roles & Groups') }}</h1>
                <p class="text-grey">Users can be assigned to roles that customize their permissions, access, and abilities throughout the Control Panel.</p>
            </div>

            <!-- Super Admin -->
             <div class="pb-5">
                <div class="flex items-center">
                    <toggle-input v-model="user.super" />
                    <label class="font-bold ml-1">Super Admin</label>
                </div>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Super admins have complete control and access to everything in the control panel. Grant this role wisely.
                </div>
            </div>

            <!-- Roles -->
            <div class="pb-5" v-if="! user.super">
                <label class="font-bold text-base mb-sm" for="role">Roles</label>
                <publish-field-meta
                    :config="{ handle: 'user.roles', type: 'user_roles' }"
                    :initial-value="user.roles">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'user.roles', type: 'user_roles' }"
                            :value="value"
                            :meta="meta"
                            name="user.roles"
                            @input="user.roles = $event" />
                    </div>
                </publish-field-meta>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Invitation') }}</h1>
                <p class="text-grey">Send a welcome email with account activiation details to the new user.</p>
            </div>

            <!-- Send Email? -->
            <div class="max-w-md mx-auto px-2 mb-3 flex items-center">
                <toggle-input v-model="invitation.send" />
                <label class="font-bold ml-1">Send Email Invitation</label>
            </div>

            <div class="max-w-lg mx-auto bg-grey-10 py-5 mb-7 border rounded-lg " v-if="invitation.send">
                <!-- Subject Line -->
                <div class="max-w-md mx-auto px-2 pb-5">
                    <label class="font-bold text-base mb-sm" for="email">Email Subject</label>
                    <input type="text" v-model="invitation.subject" class="input-text bg-white">
                </div>

                <!-- Email Content -->
                <div class="max-w-md mx-auto px-2">
                    <label class="font-bold text-base mb-sm" for="email">Email Content</label>
                    <textarea
                        class="input-text min-h-48 p-2 bg-white"
                        v-model="invitation.message"
                        v-elastic
                    />
                </div>
            </div>

            <!-- Copy Pasta -->
            <div class="max-w-md mx-auto px-2 pb-7" v-else>
                <p class="mb-1">Copy these credentials and share them with <code>{{ user.email }}</code> via your preferred method.</p>
                <textarea readonly class="input-text" v-elastic onclick="this.select()">
Activation URL: url
Username: {{ user.email }}
</textarea>
            </div>
        </div>

        <div class="border-t p-2">
            <div class="max-w-md mx-auto flex items-center justify-center">
                <button tabindex="3" class="btn mx-2 w-32" @click="previous" v-if="! onFirstStep">
                    &larr; {{ __('Previous')}}
                </button>
                <button tabindex="4" class="btn mx-2 w-32" :disabled="! canContinue" @click="next" v-if="! onLastStep">
                    {{ __('Next')}} &rarr;
                </button>
                <button tabindex="4" class="btn-primary mx-3" @click="submit" v-if="onLastStep">
                    {{ finishButtonText }}
                </button>
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
        route: {
            type: String
        }
    },

    data() {
        return {
            steps: ['User Information', 'Roles & Groups', 'Customize Invitation'],
            user: {
                email: null,
                super: true,
                roles: []
            },
            invitation: {
                send: true,
                subject: __('Activate your new Statamic account on ') + window.location.hostname,
                message: `Activate your new Statamic account on ${window.location.hostname} to begin managing this website.\n\nFor your security, the link below expires after 48 hours. After that, please contact the site administrator for a new password.`,
            },
            userExists: false
        }
    },

    computed: {
        finishButtonText() {
            return this.invitation.send ? 'Create and Send Email' : 'Create User';
        },
        isValidEmail() {
            return this.user.email && isEmail(this.user.email)
        }
    },

    methods: {
        canGoToStep(step) {
            if (step === 1) {
                return this.isValidEmail && ! this.userExists;
            } else if (step === 2) {
                return this.canGoToStep(1);
            }

            return true;
        },
        checkIfUserExists() {
            this.$axios.post(cp_url('user-exists'), {email: this.user.email}).then(response => {
                this.userExists = response.data.exists
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            });
        },
        submit() {
            let payload = {...this.user, invitation: this.invitation};

            this.$axios.post(this.route, payload).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$notify.error(error.response.data.message);
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
        this.$mousetrap.bindGlobal(['command+return'], e => {
            this.next();
        });

        this.$mousetrap.bindGlobal(['command+delete'], e => {
            this.previous();
        });
    }

}
</script>
