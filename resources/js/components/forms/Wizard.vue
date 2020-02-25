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
                <p class="text-grey" v-text="__('messages.form_wizard_intro')" />
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="form.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.form_configure_title_instructions') }}
                </div>
            </div>

            <!-- Handle -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Handle') }}</label>
                <input type="text" v-model="form.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.form_configure_handle_instructions') }}
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Fields') }}</h1>
                <p class="text-grey" v-text="__('messages.form_wizard_fields_intro')" />
            </div>

            <!-- Fields -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Blueprint') }}</label>
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
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.form_wizard_blueprint_instructions') }}
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Submissions') }}</h1>
                <p class="text-grey" v-text="__('messages.form_wizard_submissions_intro')" />
            </div>

            <!-- Name -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Store Submissions') }}</label>
                <toggle-input v-model="form.store" />
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.form_wizard_store_submissions_instructions') }}
                </div>
            </div>

            <!-- Email-->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Email Notifications') }}</label>
                <input type="email" v-model="form.email" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.form_wizard_email_notifications_instructions') }}
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
                <button tabindex="4" class="btn-primary mx-3" :disabled="! canSubmit" @click="submit" v-if="onLastStep">
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
            steps: [__('Naming'), __('Fields'), __('Submissions')],
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
        },

        canSubmit() {
            if (this.form.email) {
                return isEmail(this.form.email);
            }

            return true;
        }
    },

    methods: {
        canGoToStep(step) {
            if (step >= 1) {
                return Boolean(this.form.title && this.form.handle);
            }

            return true;
        },

        submit() {
            this.$axios.post(this.route, this.form).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    },

    watch: {
        'form.title': function(val) {
            this.form.handle = this.$slugify(val, '_');
        },
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
