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
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Usually a noun, like "Blog", "Product", or "Breakfast Foods".
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Handle</label>
                <input type="text" v-model="collection.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    How you'll reference to this collection in your templates. Cannot easily be changed.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-if="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Dates</h1>
                <p class="text-grey">You can select different date behaviors.</p>
            </div>

            <div class="max-w-md mx-auto px-2 pb-6">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.dateBehavior" value="articles" />
                    <p><strong class="text-md ml-2 font-bold">Articles</strong> &ndash; Entries with dates in the future will be private.</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.dateBehavior" value="events" />
                    <p><strong class="text-md ml-2 font-bold">Events</strong> &ndash; Entries with dates in the past will be private.</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.dateBehavior" :value="null" />
                    <p><strong class="text-md ml-2 font-bold">No dates</strong> &ndash; Entries will not have any dates.</p>
                </label>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-if="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Order</h1>
                <p class="text-grey">Choose how you want your Collection to be ordered.</p>
            </div>
            <div class="max-w-lg px-4 mx-auto pb-6 text-center">
                <div class="-mx-2 flex flex-wrap justify-center">
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-date" class="radio-box" :class="{selected: !collection.orderable}">
                            <input id="order-date" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.orderable" :value="false" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">
                                <template v-if="collection.dated">Date</template>
                                <template v-else>Alphabetical</template>
                            </h3>
                            <p class="text-2xs text-grey">
                                <template v-if="collection.dated">Entries are ordered by date.</template>
                                <template v-else>Entries are ordered alphabetically by title.</template>
                            </p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-numerical" class="radio-box" :class="{selected: collection.orderable}">
                            <input id="order-numerical" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.orderable" :value="true" />
                            <svg-icon name="arrange-number" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">Ordered</h3>
                            <p class="text-2xs text-grey">Entries are ordered sequentially and can be manually reordered.</p>
                        </label>
                    </div>
                </div>
            </div>

            <div class="max-w-md mx-auto pb-4">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-desc">
                    <input type="radio" v-model="collection.sortDirection" value="desc" id="direction-desc">
                    <p><strong class="text-md ml-2 font-bold">Descending</strong> &ndash;
                        <template v-if="collection.orderable">
                            Entries will be sorted from highest to lowest.
                        </template>
                        <template v-else-if="collection.dated">
                            Entries will be sorted from newest to oldest.
                        </template>
                        <template v-else>
                            Entries will be sorted from Z to A.
                        </template>
                    </p>
                </label>
                <label class="border-2 mt-4 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-asc">
                    <input type="radio" v-model="collection.sortDirection" value="asc" id="direction-asc">
                    <p><strong class="text-md ml-2 font-bold">Ascending</strong> &ndash;
                        <template v-if="collection.orderable">
                            Entries will be sorted from lowest to highest.
                        </template>
                        <template v-else-if="collection.dated">
                            Entries will be sorted from oldest to newest (unusual).
                        </template>
                        <template v-else>
                            Entries will be sorted from A to Z.
                        </template>
                    </p>
                </label>
            </div>
        </div>

        <div v-if="currentStep === 3">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Content Model</h1>
                <p class="text-grey">Your content model determines what field and data are stored in this collection.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Blueprint</label>
                <publish-field-meta
                    :config="{ handle: 'blueprints', type: 'blueprints' }"
                    :initial-value="collection.blueprints">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'blueprints', type: 'blueprints' }"
                            :value="value"
                            :meta="meta"
                            name="blueprints"
                            @input="collection.blueprints = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    You can pick an existing Blueprint or create a new one.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Taxonomies</label>
                <publish-field-meta
                    :config="{ handle: 'taxonomies', type: 'taxonomies' }"
                    :initial-value="collection.taxonomies">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'taxonomies', type: 'taxonomies' }"
                            :value="value"
                            :meta="meta"
                            name="taxonomies"
                            @input="collection.taxonomies = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Entries in this collection may be related to terms in these taxonomies. Fields will be automatically added to publish forms.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Template</label>
                <publish-field-meta
                    :config="{ handle: 'template', type: 'template' }"
                    :initial-value="collection.template">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'template', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            name="template"
                            @input="collection.template = $event" />
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
                    :initial-value="collection.layout">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'layout', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            name="layout"
                            @input="collection.layout = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Set your default layout.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-6">
                <label class="font-bold text-base mb-sm" for="defaultStatus">Default Status</label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-2">
                    <input type="radio" v-model="collection.defaultStatus" value="published" />
                    <p><strong class="text-md ml-2 font-bold">Published</strong> &ndash; Entries will default to <span class="text-green">published</span> status.</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center">
                    <input type="radio" v-model="collection.defaultStatus" value="draft" />
                    <p><strong class="text-md ml-2 font-bold">Draft</strong> &ndash; Entries will default to <span class="text-grey-50">draft</span> status.</p>
                </label>
            </div>
        </div>

        <div v-if="currentStep === 4">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">Routing</h1>
                <p class="text-grey">Route rules determine the URL pattern of your collection's entries.</p>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="structure">Structure</label>
                <publish-field-meta
                    :config="{ handle: 'structure', type: 'structures', max_items: 1 }"
                    :initial-value="collection.structure ? [collection.structure] : []">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'structure', type: 'structures', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            name="structure"
                            @input="collection.structure = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Choosing a structure will let your page hierarchy dictate its URLs.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="mount">Mount to an Entry</label>
                <publish-field-meta
                    :config="{ handle: 'mount', type: 'relationship', max_items: 1 }"
                    :initial-value="collection.mount ? [collection.mount] : []">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'mount', type: 'relationship', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            name="mount"
                            @input="collection.mount = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    Mounting to an entry will give you shortcuts to add and edit this collection's entries from within a Structure.
                    It will also let you use the `mount` variable in your routes, which will be the URL of the entry.
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">Route Pattern</label>
                <input type="text" v-model="collection.route" class="input-text">
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    <template v-if="collection.structure">A route is required when choosing a structure. You can also use `parent_uri` and `depth`.</template>
                    <template v-else>Routes are optional. If you don't need a URL, you don't need a route.</template>
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm">Accelerated Mobile Pages (AMP)</label>
                <label><input type="checkbox" v-model="collection.amp" /> Enable AMP</label>
                <div class="text-2xs text-grey-50 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    The AMP version of an entry will be routed to <code>{site url}/amp/{entry url}</code>
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
            steps: ['Naming', 'Dates', 'Order', 'Content Model', 'Routing'],
            collection: {
                title: null,
                handle: null,
                orderable: false,
                dated: true,
                dateBehavior: 'articles',
                sortDirection: 'desc',
                blueprints: [],
                taxonomies: [],
                template: null,
                layout: null,
                route: null,
                amp: false,
                structure: null,
                mount: null,
                defaultStatus: 'published',
            }
        }
    },

    watch: {
        'collection.title': function(val) {
            this.collection.handle = this.$slugify(val, '_');
            this.collection.route = this.collection.handle + '/{slug}';
        },

        'collection.dateBehavior': function (behavior) {
            this.collection.dated = behavior === null ? false : true;
        }
    },

    methods: {
        canGoToStep(step) {
            if (step === 1) {
                return Boolean(this.collection.title && this.collection.handle);
            }

            return true;
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
