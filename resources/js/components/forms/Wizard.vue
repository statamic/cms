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
                <p class="text-grey">A Form is a group of fields used for collecting user input.</p>
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your Form</label>
                <input type="text" v-model="form.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Usually a call to action, like "Contact Us" or "Vote for Your Favorite Porg".
                </div>
            </div>

            <!-- Handle -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="form.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this form in your templates. Cannot easily be changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Fields') }}</h1>
                <p class="text-grey">Define fields for your formset.</p>
            </div>

            <!-- Fields -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Blueprint</label>
                <publish-field-meta
                    :config="blueprintFieldConfig"
                    :initial-value="form.blueprint">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="blueprintFieldConfig"
                            :value="value"
                            :meta="meta"
                            name="blueprints"
                            @input="form.blueprint = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    You can pick an existing Blueprint or create a new one.
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Submissions') }}</h1>
                <p class="text-grey">Choose how you would like to handle form submissions.</p>
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Store Submissions</label>
                <toggle-input v-model="form.store" />
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Whether form submissions should be stored. Turn off if you only wish to get email notifications.
                </div>
            </div>

            <!-- Email-->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Email Notifications</label>
                <input type="text" v-model="form.email" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Be notified of submissions by email. TODO: Customize email headers?
                </div>
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
                    {{ __('Create Form') }}
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
            currentStep: 0,
            steps: ['Naming', 'Fields', 'Submissions'],
            form: {
                title: null,
                handle: null,
                blueprint: null,
                store: true,
                email: null,
            },
        }
    },

    computed: {
        blueprintFieldConfig() {
            return { handle: 'blueprints', type: 'blueprints', max_items: 1 };
        }
        // isValidEmail() {
        //     return this.user.email && isEmail(this.user.email)
        // }
    },

    methods: {
        canGoToStep(step) {
            if (step === 1) {
                return Boolean(this.form.title && this.form.handle);
            }

            return true;
        },

        submit() {
            this.$axios.post(this.route, this.form).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            });
        }
    },

    watch: {
        'form.title': function(val) {
            this.form.handle = this.$slugify(val, '_');
        },
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
