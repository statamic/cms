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
                <h1 class="mb-3">{{ __('Create a new Collection') }}</h1>
                <p class="text-grey" v-text="__('messages.collection_wizard_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="collection.title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_title_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Handle') }}</label>
                <input type="text" v-model="collection.handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_handle_instructions') }}
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-show="currentStep === 1">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Dates') }}</h1>
                <p class="text-grey" v-text="__('messages.collection_wizard_dates_intro')" />
            </div>

            <div class="max-w-md mx-auto px-2 pb-6">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.date_behavior" value="articles" />
                    <p><strong class="text-md ml-2 font-bold">{{ __('Articles') }}</strong> &ndash; {{ __('messages.collection_wizard_articles_description') }}</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.date_behavior" value="events" />
                    <p><strong class="text-md ml-2 font-bold">{{ __('Events') }}</strong> &ndash; {{ __('messages.collection_wizard_events_description') }}</p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-4">
                    <input type="radio" v-model="collection.date_behavior" :value="null" />
                    <p><strong class="text-md ml-2 font-bold">{{ __('No dates') }}</strong> &ndash; {{ __('messages.collection_wizard_no_dates_description') }}</p>
                </label>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-show="currentStep === 2">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Order') }}</h1>
                <p class="text-grey" v-text="__('messages.collection_wizard_order_intro')" />
            </div>
            <div class="max-w-lg px-4 mx-auto pb-6 text-center">
                <div class="-mx-2 flex flex-wrap justify-center">
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-date" class="radio-box" :class="{selected: !collection.orderable}">
                            <input id="order-date" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.orderable" :value="false" />
                            <svg-icon name="calendar" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">
                                <template v-if="collection.dated">{{ __('Date') }}</template>
                                <template v-else>{{ __('Alphabetical') }}</template>
                            </h3>
                            <p class="text-2xs text-grey">
                                <template v-if="collection.dated">{{ __('messages.collection_wizard_ordered_by_date_description') }}</template>
                                <template v-else>{{ __('messages.collection_wizard_ordered_by_title_description') }}</template>
                            </p>
                        </label>
                    </div>
                    <div class="w-full md:w-1/3 px-2 mb-2">
                        <label for="order-numerical" class="radio-box" :class="{selected: collection.orderable}">
                            <input id="order-numerical" class="absolute pin-t pin-r m-1" type="radio" v-model="collection.orderable" :value="true" />
                            <svg-icon name="arrange-number" class="w-8 h-8 mx-auto"></svg-icon>
                            <h3 class="my-2 font-bold">{{ __('Ordered') }}</h3>
                            <p class="text-2xs text-grey">{{ __('messages.collection_wizard_ordered_sequentially_description') }}</p>
                        </label>
                    </div>
                </div>
            </div>

            <div class="max-w-md mx-auto pb-4">
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-desc">
                    <input type="radio" v-model="collection.sort_direction" value="desc" id="direction-desc">
                    <p><strong class="text-md ml-2 font-bold">{{ __('Descending') }}</strong> &ndash;
                        <template v-if="collection.orderable">
                            {{ __('messages.collection_wizard_ordered_sequentially_descending') }}
                        </template>
                        <template v-else-if="collection.dated">
                            {{ __('messages.collection_wizard_ordered_date_descending') }}
                        </template>
                        <template v-else>
                            {{ __('messages.collection_wizard_ordered_alpha_descending') }}
                        </template>
                    </p>
                </label>
                <label class="border-2 mt-4 cursor-pointer border-grey-30 p-2 rounded flex items-center" for="direction-asc">
                    <input type="radio" v-model="collection.sort_direction" value="asc" id="direction-asc">
                    <p><strong class="text-md ml-2 font-bold">{{ __('Ascending') }}</strong> &ndash;
                        <template v-if="collection.orderable">
                            {{ __('messages.collection_wizard_ordered_sequentially_ascending') }}
                        </template>
                        <template v-else-if="collection.dated">
                            {{ __('messages.collection_wizard_ordered_date_ascending') }}
                        </template>
                        <template v-else>
                            {{ __('messages.collection_wizard_ordered_alpha_ascending') }}
                        </template>
                    </p>
                </label>
            </div>
        </div>

        <div v-show="currentStep === 3">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Content Model') }}</h1>
                <p class="text-grey" v-text="__('messages.collection_wizard_content_model_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Blueprints') }}</label>
                <publish-field-meta
                    :config="{ handle: 'blueprints', type: 'blueprints' }"
                    :initial-value="collection.blueprints">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'blueprints', type: 'blueprints', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            handle="blueprints"
                            @input="collection.blueprints = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_blueprints_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Taxonomies') }}</label>
                <publish-field-meta
                    :config="{ handle: 'taxonomies', type: 'taxonomies' }"
                    :initial-value="collection.taxonomies">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'taxonomies', type: 'taxonomies', mode: 'select' }"
                            :value="value"
                            :meta="meta"
                            handle="taxonomies"
                            @input="collection.taxonomies = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_taxonomies_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Template') }}</label>
                <publish-field-meta
                    :config="{ handle: 'template', type: 'template' }"
                    :initial-value="collection.template">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'template', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            handle="template"
                            @input="collection.template = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_template_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Layout') }}</label>
                <publish-field-meta
                    :config="{ handle: 'layout', type: 'template' }"
                    :initial-value="collection.layout">
                    <div slot-scope="{ meta, value, loading }">
                        <template-fieldtype
                            :config="{ handle: 'layout', type: 'template' }"
                            :value="value"
                            :meta="meta"
                            handle="layout"
                            @input="collection.layout = $event" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_layout_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-6">
                <label class="font-bold text-base mb-sm" for="default_publish_state">{{ __('Default Status') }}</label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center mb-2">
                    <input type="radio" v-model="collection.default_publish_state" value="true" />
                    <p><strong class="text-md ml-2 font-bold">{{ __('Published') }}</strong> &ndash; <span v-html="__('messages.collection_wizard_default_status_published_description')" /></p>
                </label>
                <label class="border-2 cursor-pointer border-grey-30 p-2 rounded flex items-center">
                    <input type="radio" v-model="collection.default_publish_state" value="false" />
                    <p><strong class="text-md ml-2 font-bold">Draft</strong> &ndash;  <span v-html="__('messages.collection_wizard_default_status_draft_description')" /></p>
                </label>
            </div>
        </div>

        <div v-show="currentStep === 4">
            <div class="max-w-md mx-auto px-2 py-6 text-center">
                <h1 class="mb-3">{{ __('Routing') }}</h1>
                <p class="text-grey" v-text="__('messages.collection_wizard_routing_intro')" />
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="structure">{{ __('Structure') }}</label>
                <publish-field-meta
                    :config="{ handle: 'structure', type: 'structures', max_items: 1 }"
                    :initial-value="collection.structure ? [collection.structure] : []">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'structure', type: 'structures', max_items: 1, mode: 'select' }"
                            :value="value"
                            :meta="Object.assign({}, meta, { taggable: true })"
                            handle="structure"
                            @input="collection.structure = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_structure_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="mount">{{ __('Mount to an Entry') }}</label>
                <publish-field-meta
                    :config="{ handle: 'mount', type: 'entries', max_items: 1 }"
                    :initial-value="collection.mount ? [collection.mount] : []">
                    <div slot-scope="{ meta, value, loading }">
                        <relationship-fieldtype
                            v-if="!loading"
                            :config="{ handle: 'mount', type: 'entries', max_items: 1 }"
                            :value="value"
                            :meta="meta"
                            handle="mount"
                            @input="collection.mount = $event[0]" />
                    </div>
                </publish-field-meta>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_mount_instructions') }}
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Route Pattern') }}</label>
                <input type="text" v-model="collection.route" class="input-text">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    <template v-if="collection.structure">{{ __('messages.collection_wizard_structure_route_instructions') }}</template>
                    <template v-else>{{ __('messages.collection_wizard_route_instructions') }}</template>
                </div>
            </div>
            <div class="max-w-md mx-auto px-2 pb-7">
                <label class="font-bold text-base mb-sm">{{ __('Accelerated Mobile Pages (AMP)') }}</label>
                <label><input type="checkbox" v-model="collection.amp" /> {{ __('Enable AMP') }}</label>
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    <svg-icon name="info-circle" class="mr-sm flex items-center mb-px"></svg-icon>
                    {{ __('messages.collection_wizard_amp_instructions') }}
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
            steps: [__('Naming'), __('Dates'), __('Order'), __('Content Model'), __('Routing')],
            collection: {
                title: null,
                handle: null,
                orderable: false,
                dated: true,
                date_behavior: 'articles',
                sort_direction: 'desc',
                blueprints: [],
                taxonomies: [],
                template: null,
                layout: null,
                route: null,
                amp: false,
                structure: null,
                mount: null,
                default_publish_state: true,
            }
        }
    },

    watch: {
        'collection.title': function(val) {
            this.collection.handle = this.$slugify(val, '_');
            this.collection.route = this.collection.handle + '/{slug}';
        },

        'collection.date_behavior': function (behavior) {
            this.collection.dated = behavior === null ? false : true;
        }
    },

    methods: {
        canGoToStep(step) {
            if (step >= 1) {
                return Boolean(this.collection.title && this.collection.handle);
            }

            return true;
        },
        submit() {
            this.$axios.post(this.route, this.collection).then(response => {
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
