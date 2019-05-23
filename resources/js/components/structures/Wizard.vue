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
                <h1 class="mb-3">Structures</h1>
                <p class="text-grey">Structures are heirarchial arrangements of your content, most often used to represent forms of site navigation.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your structure</label>
                <input type="text" v-model="structure.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    For example: "Pages", "Documentation", or "Navigation".
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="structure.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this structure in your templates. Cannot be easily changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Purpose</h1>
                <p class="text-grey">Structures are heirarchial arrangements of your content, most often used to represent URL structure or site navigation.</p>
            </div>
            <div class="max-w-lg px-4 mx-auto pb-6 text-center">
                <div class="-mx-2 flex flex-wrap justify-center">
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="purpose-navigation" class="radio-box" :class="{selected: purpose === 'navigation'}">
                            <input id="purpose-navigation" class="absolute pin-t pin-r m-1" type="radio" v-model="purpose" value="navigation" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Navigation</h3>
                            <p class="text-2xs text-grey">
                                Contains links to internal entries or hardcoded URLs.
                            </p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="purpose-collection" class="radio-box" :class="{selected: purpose === 'collection'}">
                            <input id="purpose-collection" class="absolute pin-t pin-r m-1" type="radio" v-model="purpose" value="collection" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Collection</h3>
                            <p class="text-2xs text-grey">
                                Controls the URL structure of a collection.
                            </p>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Contents</h1>
                <p class="text-grey">Choose the available contents of this structure.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'navigation'">
                <label class="font-bold text-base mb-sm" for="name">Collections</label>
                <publish-field-meta
                    :config="{ handle: 'collections', type: 'collections' }"
                    :initial-value="structure.collections">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collections', type: 'collections' }"
                            :value="value"
                            :meta="meta"
                            name="collections"
                            @input="structure.collections = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Any collections you select here will make its entries selectable when building your page tree.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'collection'">
                <label class="font-bold text-base mb-sm" for="name">Collection</label>
                <publish-field-meta
                    :config="{ handle: 'collection', type: 'collections', max_items: 1 }"
                    :initial-value="structure.collection ? [structure.collection] : null">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collection', type: 'collections', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            name="collections"
                            @input="structure.collection = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    If the collection you intend to use does not exist yet, just choose this structure when you create it.
                </div>
            </div>
        </div>

        <div v-if="currentStep === 3">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Pages</h1>
                <p class="text-grey">Control various options about how the pages in the structure behave.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'collection'">
                <label class="font-bold text-base mb-sm" for="name">Route Pattern</label>
                <input type="text" v-model="structure.route" class="input-text">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    The route defines the URLs for the entries in the collection. You can use `parent_uri` to if you plan to nest pages.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Max Depth</label>
                <input type="number" min="1" step="1" v-model="structure.max_depth" class="input-text">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    The deepest a page may be nested
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
                    {{ __('Create structure')}}
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
            steps: ['Naming', 'Purpose', 'Contents', 'Pages'],
            currentStep: 0,
            purpose: null,
            structure: {
                title: null,
                handle: null,
                collections: [],
                collection: null,
                max_depth: null,
                route: '{parent_uri}/{slug}',
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
        'structure.title': function(val) {
            this.structure.handle = this.$slugify(val, '_');
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
                return Boolean(this.structure.title && this.structure.handle);
            }

            if (step === 2) {
                return Boolean(this.purpose);
            }

            if (step === 3) {
                if (this.purpose === 'navigation' && this.structure.collections.length === 0) return false;

                return true;
            }

            return true;
        },
        submit() {
            this.$axios.post(this.route, this.structure).then(response => {
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
