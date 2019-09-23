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
                <h1 class="mb-3" v-text="__('Structures')" />
                <p class="text-grey">
                    {{ __('Structures are link trees most often used to create nav bars and other forms of site navigation.') }}
                </p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name" v-text="__('Structure Name')" />
                <input type="text" v-model="structure.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    For example: "Pages", "Main Nav Bar", or "Documentation".
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name" v-text="__('Handle')" />
                <input type="text" v-model="structure.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this structure in your templates. Cannot easily be changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3" v-text="__('Purpose')" />
                <p class="text-grey">Structures can either control a collection with parent/child URLs, or define hand-crafted nav trees from existing entries and user-defined URLs.</p>
            </div>
            <div class="max-w-lg px-4 mx-auto pb-6 text-center">
                <div class="-mx-2 flex flex-wrap justify-center">
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="purpose-collection" class="radio-box" :class="{selected: purpose === 'collection'}">
                            <input id="purpose-collection" class="absolute pin-t pin-r m-1" type="radio" v-model="purpose" value="collection" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold" v-text="__('Collection')" />
                            <p class="text-2xs text-grey">
                                Control a collection's URLs with parent/child relationships.
                            </p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="purpose-navigation" class="radio-box" :class="{selected: purpose === 'navigation'}">
                            <input id="purpose-navigation" class="absolute pin-t pin-r m-1" type="radio" v-model="purpose" value="navigation" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold" v-text="__('Navigation')" />
                            <p class="text-2xs text-grey">
                                Create a tree of links to existing entries and/or user-defined URLs.
                            </p>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Settings</h1>
                <p class="text-grey">Define which collections are available to this Structure and how deep it can grow.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'navigation'">
                <label class="font-bold text-base mb-sm" for="name" v-text="__('Collections')" />
                <publish-field-meta
                    :config="{ handle: 'collections', type: 'collections' }"
                    :initial-value="structure.collections">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collections', type: 'collections' }"
                            :value="value"
                            :meta="meta"
                            handle="collections"
                            @input="structure.collections = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Entries from selected collections will be available to choose from when building the page tree.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'collection'">
                <label class="font-bold text-base mb-sm" for="name" v-text="__('Collection')" />
                <publish-field-meta
                    :config="{ handle: 'collection', type: 'collections', max_items: 1 }"
                    :initial-value="structure.collection ? [structure.collection] : null">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'collection', type: 'collections', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            handle="collections"
                            @input="structure.collection = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    <span>If the collection you intend to use does not exist yet, choose <b>this structure</b> later while creating it.</span>
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7" v-if="purpose === 'collection'">
                <label class="font-bold text-base mb-sm" for="name">Expect a root page</label>
                <toggle-fieldtype
                    handle="expects_root"
                    v-model="structure.expects_root"  />
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Whether you want the first page in the tree to be considered the "root" or "home" page.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Max Depth</label>
                <input type="number" min="1" step="1" v-model="structure.max_depth" class="input-text">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    The maximum number of levels deep a page may be nested. Leave blank for no limit.
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
                <button tabindex="4" class="btn-primary mx-3" :disabled="! canComplete" @click="submit" v-if="onLastStep">
                    {{ __('Create structure')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
// Yer a wizard Harry

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
            steps: ['Naming', 'Purpose', 'Settings'],
            purpose: null,
            structure: {
                title: null,
                handle: null,
                collections: [],
                collection: null,
                max_depth: null,
                route: '{parent_uri}/{slug}',
                expects_root: null,
            }
        }
    },

    computed: {
        canComplete() {
            return this.canGoToStep(3);
        }
    },

    watch: {
        'structure.title': function(val) {
            this.structure.handle = this.$slugify(val, '_');
        },

        purpose(purpose) {
            if (purpose === 'collection') {
                this.structure.expects_root = true;
            }
        }
    },

    methods: {
        canGoToStep(step) {
            if (step === 1) {
                return Boolean(this.structure.title && this.structure.handle);
            }

            if (step === 2) {
                return Boolean(this.purpose);
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
