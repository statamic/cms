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
                <h1 class="mb-3">Create a new Taxonomy</h1>
                <p class="text-grey">A Taxonomy is a system of classifying data around a set of unique characteristics, such as category or color.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your Taxonomy</label>
                <input type="text" v-model="taxonomy.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Usually a noun, like "Categories" or "Tags".
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="taxonomy.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this taxonomy in your templates. Cannot be easily changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Content Model</h1>
                <p class="text-grey">Your content model determines what field and data are stored in this taxonomy.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Blueprint</label>
                <publish-field-meta
                    :config="{ handle: 'term_blueprint', type: 'blueprints' }"
                    :initial-value="taxonomy.term_blueprint ? [taxonomy.term_blueprint] : []">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'term_blueprint', type: 'blueprints', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            name="term_blueprint"
                            @input="taxonomy.term_blueprint = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    You can pick an existing Blueprint or creates a new one.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Template</label>
                <publish-field-meta
                    :config="{ handle: 'template', type: 'template' }"
                    :initial-value="taxonomy.template">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'template', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            name="template"
                            @input="taxonomy.template = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Set your default template.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Layout</label>
                <publish-field-meta
                    :config="{ handle: 'layout', type: 'template' }"
                    :initial-value="taxonomy.layout">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'layout', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            name="layout"
                            @input="taxonomy.layout = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Set your default layout.
                </div>
            </div>
        </div>

        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Routing</h1>
                <p class="text-grey">Route rules determine the URL pattern of your taxonomy's entries.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Route Pattern</label>
                <input type="text" v-model="taxonomy.route" class="input-text">
            </div>
        </div>

        <div v-if="currentStep === 3">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Collections</h1>
                <p class="text-grey">Create the links between taxonomy and collection.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Collections</label>
                <publish-field-meta
                    :config="{ handle: 'collections', type: 'collections' }"
                    :initial-value="taxonomy.collections">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collections', type: 'collections' }"
                            :value="value"
                            :meta="meta"
                            name="collections"
                            @input="taxonomy.collections = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    When a collection is linked to a taxonomy, its entries will automatically get fields added to their forms.
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
            steps: ['Naming', 'Content Model', 'Routing', 'Collections'],
            currentStep: 0,
            taxonomy: {
                title: null,
                handle: null,
                term_blueprint: null,
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
