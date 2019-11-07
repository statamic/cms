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
        <div v-show="currentStep === 0">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Create a new Taxonomy') }}</h1>
                <p class="text-grey" v-text="__('messages.taxonomy_wizard_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="taxonomy.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.taxonomy_wizard_title_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Handle') }}</label>
                <input type="text" v-model="taxonomy.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.taxonomy_wizard_handle_instructions') }}
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-show="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Content Model') }}</h1>
                <p class="text-grey" v-text="__('messages.taxonomy_wizard_content_model_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Blueprint') }}</label>
                <publish-field-meta
                    :config="{ handle: 'blueprints', type: 'blueprints' }"
                    :initial-value="taxonomy.blueprints">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'blueprints', type: 'blueprints', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            handle="blueprints"
                            @input="taxonomy.blueprints = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.taxonomy_wizard_blueprint_instructions') }}
                </div>
            </div>
        </div>

        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Collections') }}</h1>
                <p class="text-grey" v-text="__('messages.taxonomy_wizard_collections_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Collections') }}</label>
                <publish-field-meta
                    :config="{ handle: 'collections', type: 'collections' }"
                    :initial-value="taxonomy.collections">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collections', type: 'collections' }"
                            :value="value"
                            :meta="meta"
                            handle="collections"
                            @input="taxonomy.collections = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.taxonomy_wizard_collections_instructions') }}
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
                    {{ __('Create Taxonomy')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
// Yer a wizard Harry
export default {
    props: {
        route: {
            type: String
        }
    },

    data() {
        return {
            steps: [__('Naming'), __('Content Model'), __('Collections')],
            currentStep: 0,
            taxonomy: {
                title: null,
                handle: null,
                blueprints: [],
                template: null,
                layout: null,
                route: null,
                collections: [],
            }
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
    },

    watch: {
        'taxonomy.title': function(val) {
            this.taxonomy.handle = this.$slugify(val, '_');
            this.taxonomy.route = this.taxonomy.handle + '/{slug}';
        },

        'taxonomy.dateBehavior': function (behavior) {
            this.taxonomy.dated = behavior === null ? false : true;
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
                return Boolean(this.taxonomy.title && this.taxonomy.handle);
            }

            return true;
        },
        submit() {
            this.$axios.post(this.route, this.taxonomy).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$toast.error(error.response.data.message);
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
