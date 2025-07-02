<template>
    <div>
        <ui-header :title="__('Edit Blueprint')" icon="blueprints">
            <template #actions>
                <ui-button type="submit" variant="primary" @click.prevent="save" v-text="__('Save')" />
            </template>
        </ui-header>

        <ui-panel :heading="__('Settings')">
            <ui-card>
                <ui-field
                    :label="__('Title')"
                    :instructions="__('messages.blueprints_title_instructions')"
                    :errors="errors.title"
                >
                    <ui-input v-model="blueprint.title" />
                </ui-field>
            </ui-card>
            <ui-card class="mt-2">
                <ui-field
                    :label="__('Hidden')"
                    :instructions="__('messages.blueprints_hidden_instructions')"
                    :error="errors.hidden"
                    variant="inline"
                >
                    <ui-switch v-model="blueprint.hidden" />
                </ui-field>
            </ui-card>
        </ui-panel>

        <Tabs
            class="mt-8"
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
