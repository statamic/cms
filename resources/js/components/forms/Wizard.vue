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
                <h1 class="mb-3">{{ __('Create a New Form') }}</h1>
                <p class="text-grey">Create a new form description.</p>
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your Form</label>
                <input type="text" v-model="form.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Form name help text.
                </div>
            </div>

            <!-- Handle -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="form.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this form in your templates. Cannot be easily changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->

        <!-- Step 3 -->

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
    props: {
        route: {
            type: String
        }
    },

    data() {
        return {
            steps: ['Naming', '', ''],
            form: {
                title: null,
                handle: null,
            },
            customizedMessage: null,
            userExists: false
        }
    },

    computed: {
        message: {
            get() {
                return this.customizedMessage || `Activate your new Statamic account on ${window.location.hostname} to begin managing this website.

For your security, the link below expires after 48 hours. After that, please contact the site administrator for a new password.`
            },
            set(message) {
                this.customizedMessage = message;
            }
        },
        // finishButtonText() {
        //     return this.send_invite ? 'Create and Send Email' : 'Create User';
        // },
        // isValidEmail() {
        //     return this.user.email && isEmail(this.user.email)
        // }
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
            let payload = {subject: this.email_subject, message: this.message, ...this.user};

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
