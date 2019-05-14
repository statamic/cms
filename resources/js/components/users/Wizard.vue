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
                <input type="email" v-model="user.email" id="email" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Email addresses serve as usernames and must be unique
                </div>
            </div>

             <div class="max-w-md mx-auto px-2 pb-3">
                <div class="flex items-center">
                    <toggle-input v-model="user.super_admin" />
                    <label class="font-bold ml-1">Super Admin</label>
                </div>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Super admins have complete control and access to everything in the control panel. Grant this role wisely.
                </div>
            </div>

            <!-- Roles -->
            <div class="max-w-md mx-auto px-2 pb-3" v-if="! user.super_admin">
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
                            @updated="user.roles = $event" />
                    </div>
                </publish-field-meta>
            </div>

            <div class="max-w-md mx-auto px-2 pb-5">
                <div class="flex items-center">
                    <toggle-input v-model="generate_password" />
                    <label class="font-bold ml-1">Automatically generate password</label>

                </div>
                <div class="mt-3" v-if="! generate_password">
                    <label class="font-bold text-base mb-sm" for="name">{{ __('Password')}}</label>
                    <input type="text" v-model="customizedPassword" class="input-text" tabindex="2">
                </div>
                <div class="text-2xs text-grey-60 mt-1 mb-3 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    User will be required to change their password after sign-in
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Invitation') }}</h1>
                <p class="text-grey">Send credentials and login details to the new user.</p>
            </div>
            <div class="max-w-md mx-auto px-2 mb-3 flex items-center">
                <toggle-input v-model="send_invite" />
                <label class="font-bold ml-1">Send email invite</label>
            </div>

            <div class="max-w-lg mx-auto bg-grey-10 py-5 mb-7 border rounded-lg " v-if="send_invite">
                <div class="max-w-md mx-auto px-2 pb-5">
                    <label class="font-bold text-base mb-sm" for="email">Email Subject</label>
                    <input type="text" v-model="email_subject" class="input-text bg-white">
                </div>

                <div class="max-w-md mx-auto px-2">
                    <label class="font-bold text-base mb-sm" for="email">Email Content</label>
                    <textarea
                        class="input-text min-h-48 p-2 bg-white"
                        v-model="message"
                        v-elastic
                    />
                </div>
            </div>

            <div class="max-w-md mx-auto px-2 pb-7" v-if="!send_invite">
                <p class="mb-1">Copy these credentials and share them with <code>{{ user.email }}</code> via your preferred method.</p>
                <textarea readonly class="input-text" v-elastic onclick="this.select()">
Login URL: url
Username: {{ user.email }}
Password: {{ password }}</textarea>
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
var generator = require('generate-password');

export default {
    props: {
        route: {
            type: String
        }
    },

    data() {
        return {
            steps: ['User Information', 'Customize Invitation'],
            currentStep: 0,
            user: {
                email: null,
                super_admin: true,
                roles: []
            },
            generate_password: true,
            send_invite: true,
            customizedPassword: null,
            email_subject: __('You have a new Statamic account on ') + window.location.hostname,
            customizedMessage: null
        }
    },

    computed: {
        onFirstStep() {
            return this.currentStep === 0;
        },
        onLastStep() {
            return this.currentStep === this.steps.length - 1;
        },
        canContinue() {
            return this.canGoToStep(this.currentStep + 1);
        },
        password() {
            return this.customizedPassword || generator.generate({length: 12, numbers: true})
        },
        message: {
            get() {
                return this.customizedMessage || `You have a new Statamic account on ${window.location.hostname}. Sign in to begin managing the website.

**Your username**
${this.user.email}

**Your temporary password**
${this.password}

For your security, this link expires after 48 hours. After that, please contact the site administrator for a new password.`
            },
            set(message) {
                this.customizedMessage = message;
            }
        },
        finishButtonText() {
            return this.send_invite ? 'Create and Send Email' : 'Create User';
        }
    },

    methods: {
        goToStep(n) {
            if (this.canGoToStep(n)) {
                this.currentStep = n;
            }
        },
        next() {
            if (! this.onLastStep) {
                this.goToStep(this.currentStep + 1);
            }
        },
        previous() {
            if (! this.onFirstStep) {
                this.goToStep(this.currentStep - 1);
            }
        },
        canGoToStep(step) {
            if (step === 1) {
                return Boolean(this.user.email) && (this.generate_password || (!this.generate_password && this.customizedPassword));
            }

            return true;
        },
        submit() {
            this.$axios.post(this.route, this.user).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            });
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
