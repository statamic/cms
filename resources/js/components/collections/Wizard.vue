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
                <h1 class="mb-3">Create a new Collection</h1>
                <p class="text-grey">A Collection is a group of entries that holds similar content and shares behavior and attributes, like URL patterns, ordering, and visibility.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your Collection</label>
                <input type="text" v-model="collection.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-40 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Usually a noun, like "Blog", "Product", or "Breakfast Foods".
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="collection.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-40 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this collection in your templates. Cannot be easily changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Ordering</h1>
                <p class="text-grey">Each Collection can behave differently based on way you prefer your Entries to be ordered and grouped.</p>
            </div>
            <div class="max-w-lg px-4 mx-auto pb-6 text-center">
                <div class="-mx-2 flex flex-wrap">
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-date" class="radio-box" :class="{selected: collection.order === 'date'}">
                            <input id="order-date" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.order" value="date" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Date</h3>
                            <p class="text-2xs text-grey">Entries are ordered by date and can be automatically published and expired.</p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-alpha" class="radio-box" :class="{selected: collection.order === 'alphabetical'}">
                            <input id="order-alpha" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.order" value="alphabetical" />
                            <svg-icon name="arrange-letter" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Alphabetical</h3>
                            <p class="text-2xs text-grey">Entries are ordered alphabetically by title and can be grouped by letter.</p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-numerical" class="radio-box" :class="{selected: collection.order === 'numerical'}">
                            <input id="order-numerical" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.order" value="numerical" />
                            <svg-icon name="arrange-number" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Numerical</h3>
                            <p class="text-2xs text-grey">Entries are ordered sequentally and can be manually reordered.</p>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Behavior</h1>
                <p class="text-grey">Each Collection can behave differently based on way you prefer your Entries to be ordered and grouped.</p>
            </div>
            <!-- Date Collection -->
            <div class="max-w-md mx-auto px-2 pb-6" v-if="collection.order == 'date'">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="behavior-scheduled">
                    <input type="checkbox" v-model="collection.behavior.scheduled" id="behavior-scheduled">
                    <p><strong class="text-md ml-2 font-bold">Scheduled</strong> &ndash; Entries with publish dates in the future will be private.</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 mt-4 rounded flex items-center" for="behavior-expirable">
                    <input type="checkbox" v-model="collection.behavior.expirable" id="behavior-expirable">
                    <p><strong class="text-md ml-2 font-bold">Expirable</strong> &ndash; Entries can be expired and made private after a specified date.</p>
                </label>
            </div>
            <!-- Alphabetical Collection -->
            <div class="max-w-md mx-auto px-2 pb-6" v-if="collection.order == 'alphabetical'">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-asc">
                    <input type="radio" v-model="collection.behavior.direction" value="asc" id="direction-asc">
                    <p><strong class="text-md ml-2 font-bold">Ascending</strong> &ndash; Entries will be sorted in ascending order, from A to Z.</p>
                </label>
                <label class="border-2 mt-4 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-desc">
                    <input type="radio" v-model="collection.behavior.direction" value="desc" id="direction-desc">
                    <p><strong class="text-md ml-2 font-bold">Descending</strong> &ndash; Entries will be sorted in descending order, from Z to A.</p>
                </label>
            </div>
        </div>

        <div v-if="currentStep === 3">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Content Model</h1>
                <p class="text-grey">Your content model determines what field and data are stored in this collection.</p>
            </div>
            <!-- <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Blueprint</label>
                <blueprints-fieldtype
                    name="blueprint"
                    :config="{ max_items: 1 }"
                    :value="blueprint ? [blueprint] : null"
                    @updated="blueprint = $event[0]"
                ></blueprints-fieldtype>
                <div class="text-2xs text-grey-40 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    You can pick an existing Blueprint or creates a new one.
                </div>
            </div> -->
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Template</label>
                <select v-model="collection.template">
                    <option value="" selected>Choose a template</option>
                    <option value="simple">Simple Page</option>
                </select>
                <div class="text-2xs text-grey-40 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Set your default template.
                </div>
            </div>
        </div>

        <div v-if="currentStep === 4">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Route</h1>
                <p class="text-grey">Route rules determine the URL pattern of your collection's entries.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Route Pattern</label>
                <input type="text" v-model="collection.route" class="input-text">
                <div class="text-2xs text-grey-40 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Routes are optional. If you don't need a URL, you don't need a route.
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
                    {{ __('Create Collection')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
// Yer a wizard Harry
export default {
    props: {
        steps: {
            type: Array
        },
        route: {
            type: String
        }
    },

    data() {
        return {
            currentStep: 0,
            collection: {
                title: null,
                handle: null,
                order: null,
                fieldset: null,
                blueprint: null,
                template: null,
                route: null,
                behavior: {}
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
        'collection.title': function(val) {
            this.collection.handle = this.$slugify(val, '_');
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
            if (step === 0) {
                return true;
            } else if (step === 1) {
                return Boolean(this.collection.title && this.collection.handle);
            } else if (step === 2) {
                return Boolean(this.canGoToStep(1) && this.collection.order);
            } else if (step === 3) {
                return Boolean(this.canGoToStep(2)
                    && (this.collection.order == "date") || this.collection.order == "numerical" || this.collection.behavior.hasOwnProperty('direction')
                );
            } else if (step === 4) {
                return Boolean(this.canGoToStep(3));
            }

            return false;
        },
        submit() {
            this.$axios.post(this.route, this.collection).then(response => {
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
