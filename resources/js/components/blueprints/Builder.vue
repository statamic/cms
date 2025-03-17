<template>
    <div class="blueprint-builder">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <h1 v-text="__('Edit Blueprint')" />
                <button type="submit" class="btn-primary" @click.prevent="save" v-text="__('Save')" />
            </div>
        </header>

        <div class="publish-form card mb-8 p-0 @container" v-if="showTitle">
            <div class="publish-fields">
                <div class="form-group config-field">
                    <div class="field-inner">
                        <label class="block">{{ __('Title') }}</label>
                        <p class="help-block">{{ __('messages.blueprints_title_instructions') }}</p>
                        <div v-if="errors.title">
                            <p
                                class="help-block text-red-500"
                                v-for="(error, i) in errors.title"
                                :key="i"
                                v-text="error"
                            />
                        </div>
                    </div>
                    <div>
                        <input
                            type="text"
                            name="title"
                            class="input-text"
                            v-model="blueprint.title"
                            autofocus="autofocus"
                        />
                    </div>
                </div>

                <div class="form-group config-field">
                    <div class="field-inner">
                        <label class="block">{{ __('Hidden') }}</label>
                        <p class="help-block">{{ __('messages.blueprints_hidden_instructions') }}</p>
                        <div v-if="errors.hidden">
                            <p
                                class="help-block text-red-500"
                                v-for="(error, i) in errors.hidden"
                                :key="i"
                                v-text="error"
                            />
                        </div>
                    </div>
                    <div>
                        <toggle-input name="hidden" v-model="blueprint.hidden" />
                    </div>
                </div>
            </div>
        </div>

        <tabs
            :single-tab="!useTabs"
            :initial-tabs="tabs"
            :errors="errors.tabs"
            :can-define-localizable="canDefineLocalizable"
            @updated="tabsUpdated"
        />
    </div>
</template>

<script>
import SuggestsConditionalFields from './SuggestsConditionalFields';
import Tabs from './Tabs.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

export default {
    mixins: [SuggestsConditionalFields, CanDefineLocalizable],

    components: {
        Tabs,
    },

    props: {
        action: String,
        initialBlueprint: Object,
        showTitle: Boolean,
        useTabs: { type: Boolean, default: true },
        isFormBlueprint: { type: Boolean, default: false },
    },

    data() {
        return {
            blueprint: this.initializeBlueprint(),
            errors: {},
        };
    },

    computed: {
        tabs() {
            return this.blueprint.tabs;
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });

        if (this.isFormBlueprint) {
            Statamic.$config.set('isFormBlueprint', true);
        }
    },

    watch: {
        blueprint: {
            deep: true,
            handler() {
                this.$dirty.add('blueprints');
            },
        },
    },

    methods: {
        initializeBlueprint() {
            let blueprint = clone(this.initialBlueprint);

            if (!this.showTitle) delete blueprint.title;

            return blueprint;
        },

        tabsUpdated(tabs) {
            this.blueprint.tabs = tabs;
        },

        save() {
            // this.$axios[this.method](this.action, this.fieldset)
            this.$axios['patch'](this.action, this.blueprint)
                .then((response) => this.saved(response))
                .catch((e) => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                });
        },

        saved(response) {
            this.$toast.success(__('Saved'));
            this.errors = {};
            this.$dirty.remove('blueprints');
        },
    },
};
</script>
