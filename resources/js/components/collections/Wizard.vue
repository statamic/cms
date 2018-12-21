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
            <div class="max-w-md mx-auto py-6 text-center">
                <h1 class="mb-3">Create a new Collection</h1>
                <p class="text-grey">A Collection is a group of entries that holds similar content and shares behavior and attributes, like URL patterns, ordering, and visibility.</p>
            </div>
            <div class="max-w-md mx-auto pb-7">
                <label class="font-bold text-base mb-sm" for="name">Name of your Collection</label>
                <input type="text" v-model="title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-light mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Usually a noun, like "Blog", "Product", or "Breakfast Foods".
                </div>
            </div>
            <div class="max-w-md mx-auto pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-light mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this collection in your templates. Cannot be easily changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto py-6 text-center">
                <h1 class="mb-3">Ordering</h1>
                <p class="text-grey">Each Collection can behave differently based on way you prefer your Entries to be ordered and grouped.</p>
                <div class="-mx-3 flex flex-wrap mt-6">
                    <div class="w-full md:w-1/3 px-2">
                        <label for="sort-date" class="border rounded cursor-pointer relative p-2">
                            <input id="sort-date" class="absolute pin-t pin-r m-1" type="radio" name="sort" v-model="sort" value="date" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Date</h3>
                            <p class="text-2xs text-grey">Entries are ordered by date and can be automatically published and expired.</p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2">
                        <label for="sort-alpha" class="border rounded cursor-pointer relative p-2">
                            <input id="sort-alpha" class="absolute pin-t pin-r m-1" type="radio" name="sort" v-model="sort" value="alphabetical" />
                            <svg-icon name="arrange-letter" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Alphabetical</h3>
                            <p class="text-2xs text-grey">Entries are ordered alphabetically by title and can be grouped by letter.</p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2">
                        <label for="sort-numerical" class="border rounded cursor-pointer relative p-2">
                            <input id="sort-numerical" class="absolute pin-t pin-r m-1" type="radio" name="sort" v-model="sort" value="numerical" />
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
            <div class="max-w-md mx-auto py-6 text-center">
                <h1 class="mb-3">Behavior</h1>
                <p class="text-grey">Each Collection can behave differently based on way you prefer your Entries to be ordered and grouped.</p>

            </div>
        </div>

        <div class="border-t p-2">
            <div class="max-w-md mx-auto flex items-center justify-center">
                <button tabindex="3" class="btn-primary mx-2 w-32" @click="previous" v-if="! onFirstStep">
                    &larr; {{ __('Previous')}}
                </button>
                <button tabindex="4" class="btn-primary mx-2 w-32" :disabled="! canContinue" @click="next" v-if="! onLastStep">
                    {{ __('Next')}} &rarr;
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
    },

    data() {
        return {
            currentStep: 0,
            title: null,
            handle: null,
            sort: null
        }
    },

    computed: {
        onFirstStep() {
            return this.currentStep === 0;
        },
        onLastStep() {
            return this.currentStep === this.steps.length;
        },
        canContinue() {
            return this.canGoToStep(this.currentStep + 1);
        },
    },

    watch: {
        title: function(val) {
            this.handle = this.$slugify(val, '_');
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
            switch (step) {
                case 0:
                    return true;
                    break;
                case 1:
                    return this.title && this.handle;
                    break;
                case 2:
                    return this.canGoToStep(1) && this.sort;
                default:
                    return false;
                    break;
            }
        },
    }
}
</script>
